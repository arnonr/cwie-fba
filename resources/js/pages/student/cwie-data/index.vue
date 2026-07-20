<script setup>
import PersonalData from "@/components/student-view/PersonalData.vue"
import StudentAction from "@/components/student-view/StudentAction.vue"
import axios from "@axios"
import { useRoute, useRouter } from "vue-router"
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"
import { useStudentViewStore } from "./useCwieDataViewStore"
import { form_statuses, statusShow } from "@/data-constant/data"

dayjs.extend(buddhistEra)

const studentViewStore = useStudentViewStore()
const route = useRoute()
const router = useRouter()

// const emit = defineEmits(["refresh-data", "close"]);

// var UI
const currentStep = ref(0)

const formSteps = [
  {
    title: "ข้อมูลใบสมัคร",
    size: 24,
    icon: "tabler-file",
  },
  {
    title: "ข้อมูลเอกสารตอบรับ",
    size: 24,
    icon: "tabler-checklist",
    isActiveStepValid: false,
  },
  {
    title: "ข้อมูลแผนการปฏิบัติงาน",
    size: 24,
    icon: "tabler-notes",
  },
]

const prependIcon = "tabler-arrow-big-right-filled"

const qualifications = [
  {
    title:
      "ผ่านรายวิชาเตรียมสหกิจศึกษา หรืออยู่ระหว่างเรียนวิชาเตรียมสหกิจศึกษา",
    label: "is_pass_coop_subject",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "ผ่านรายวิชาหมวดศึกษาทั่วไปและหมวดวิชาเฉพาะ ไม่น้อยกว่า 15, 60 หน่วยกิต ตามลำดับ",
    label: "is_pass_general_subject",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "มีเกรดเฉลี่ยไม่ต่ำกว่า 2.00 นับถึงภาคการศึกษาสุดท้ายก่อนออกฝึกสหกิจศึกษา",
    label: "is_pass_gpa",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "ไม่อยู่ในระหว่างถูกพักการศึกษาในภาคการศึกษาที่เข้าร่วมโครงการสหกิจศึกษา",
    label: "is_pass_suspend",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title: "ไม่เคยถูกลงโทษทางวินัย หรือกระทำผิดกฏหมายอาญาร้ายแรง",
    label: "is_pass_punishment",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title: "ไม่เป็นโรคที่เป็นอุปสรรคต่อการปฏิบัติงานในสถานประกอบการ",
    label: "is_pass_disease",
    props: {
      prependIcon: prependIcon,
    },
  },
]

const isOverlay = ref(false)
const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")
const isCheck = ref(false)
const company = ref({})
const isClose = ref(true)
const student_id = ref(null)
const student = ref({})

let userData = JSON.parse(localStorage.getItem("userData"))
const formActive = ref(null)
const items = ref([])
const provinces = ref([])
const amphurs = ref([])
const tumbols = ref([])
const status_id = ref(null)

// const selectOptions = ref({});

// 👉 Fetching
const fetchProvince = async () => {
  let res = await axios.get("/province", {
    validateStatus: () => true,
  })
  provinces.value = res.data.data
}

fetchProvince()

const fetchAmphur = async () => {
  let res = await axios.get("/amphur", {
    validateStatus: () => true,
  })
  amphurs.value = res.data.data
}

fetchAmphur()

const fetchTumbol = async () => {
  let res = await axios.get(
    "/tumbol",
    { perPage: 20000 },
    {
      validateStatus: () => true,
    },
  )
  tumbols.value = res.data.data
}

fetchTumbol()

const fetchStudent = () => {
  studentViewStore
    .fetchStudents({
      student_code: userData.username.slice(1, userData.username.length),
      includeAll: true,
    })
    .then(response => {
      if (response.data.message == "success") {
        const { data } = response.data

        student.value = data[0]
        student_id.value = data[0].id
        fetchForms()
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

fetchStudent()

const fetchForms = () => {
  studentViewStore
    .fetchForms({
      student_id: student_id.value,
      perPage: 20,
      currentPage: 1,
      orderBy: "id",
      order: "desc",
      includeAll: true,
    })
    .then(async response => {
      if (response.data.message == "success") {
        const { data } = response.data

        items.value = data

        if (data.length != 0) {
          if (data[0].active == 1) {
            await studentViewStore
              .fetchCompany({ id: data[0].company_id })
              .then(response => {
                if (response.status === 200) {
                  company.value = response.data.data
                } else {
                  console.log("error")
                }
              })
              .catch(error => {
                console.error(error)
                isOverlay.value = false
              })

            await studentViewStore
              .fetchMajorHeads({
                semester_id: data[0].semester_id,
                major_id: student.value.major_id,
                active: 1,
                includeAll: true,
              })
              .then(res => {
                data[0].major_head_name = res.data.data[0].teacher_name
              })

            let i = 0
            qualifications.forEach(qf => {
              data[0][qf.label] = data[0][qf.label] == 1 ? true : false
              if (data[0][qf.label] == 1) {
                i = i + 1
              }
            })
            if (i == 6) {
              isCheck.value = true
            } else {
              isCheck.value = false
            }
            status_id.value = data[0].status_id
            if (data[0].namecard_file != null) {
              let namecard_file = data[0].namecard_file.split(".")
              if (namecard_file[namecard_file.length - 1] == "pdf") {
                data[0].namecard_file_type = "pdf"
              }
            }

            formActive.value = data[0]
          }
        }
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const reject_status_show = reject_status_id => {
  if (reject_status_id) {
    if (reject_status_id == 1) {
      return "อาจารย์ที่ปรึกษา"
    }

    if (reject_status_id == 2) {
      return "ประธานอาจารย์นิเทศ"
    }

    if (reject_status_id == 3) {
      return "เจ้าหน้าที่คณะ"
    }

    if (reject_status_id == 4) {
      return "เอกสารตอบรับ"
    }

    if (reject_status_id == 5) {
      return "แผนการปฏิบัติงาน"
    }
  }
  
  return ""
}

const getProvince = province_id => {
  if (province_id == null) return ""
  let res = provinces.value.find(e => {
    return e.province_id == province_id
  })
  
  return res.name_th
}

const getAmphur = id => {
  if (id == null || amphurs.value.length == 0) return ""
  let res = amphurs.value.find(e => {
    return e.amphur_id == id
  })
  
  return res.name_th
}

const getTumbol = id => {
  if (id == null || tumbols.value.length == 0) return ""
  let res = tumbols.value.find(e => {
    return e.tumbol_id == id
  })
  
  return res.name_th
}

const onQualifications = (label, isCheck1) => {
  let dataSend = {
    id: formActive.value.id,
  }

  dataSend[label] = isCheck1 == true ? 1 : 0

  studentViewStore
    .editForm(dataSend)
    .then(response => {
      if (response.data.message == "success") {
        nextTick(() => {
          fetchForms()
        })
      } else {
        isOverlay.value = false
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
    })
}

onMounted(() => {
  window.scrollTo(0, 0)
})

const refreshData = () => {
  fetchStudent()
  if (isClose.value == false) {
    isClose.value = true
  } else {
    setTimeout(emit("close"), 4000)
  }

  //
}
</script>

<template>
  <div>
    <VRow>
      <VCol
        cols="12"
        md="12"
      >
        <PersonalData
          v-if="student_id"
          :student_id="student_id"
          :status_id="status_id"
        />
      </VCol>

      <VCol
        cols="12"
        md="12"
      >
        <!-- Action -->
        <StudentAction
          v-if="student_id"
          :student_id="student_id"
          :student="student"
          :form-active="formActive"
          :is-check="isCheck"
          @refresh-data="refreshData"
          @change-close="isClose = false"
        />
      </VCol>

      <!-- formActive -->
      <VCol
        v-if="formActive != null"
        cols="12"
        md="12"
      >
        <VCard class="pa-5">
          <VCardText>
            <h3>ครั้งที่ {{ items.length }} (ปัจจุบัน)</h3>
          </VCardText>
          <VCardText>
            <AppStepper
              v-model:current-step="currentStep"
              class="checkout-stepper"
              :items="formSteps"
              :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
            />
          </VCardText>

          <VDivider />
          <VCardText>
            <!-- 👉 stepper content -->
            <VWindow
              v-model="currentStep"
              class="disable-tab-transition"
            >
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip
                        label
                        :color="form_statuses[formActive.status_id]"
                      >
                        {{
                          statusShow(
                            formActive.status_id,
                            formActive.request_document_date,
                            formActive.confirm_response_at
                          )
                        }}</VChip>
                    </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ปีการศึกษา : </span>
                    <span>
                      {{ formActive.semester_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>อาจารย์นิเทศ : </span>
                    <span>
                      {{ formActive.supervision_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                    <span>
                      {{
                        dayjs(formActive.start_date)
                          .locale("th")
                          .format("DD MMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                    <span>
                      {{
                        dayjs(formActive.end_date)
                          .locale("th")
                          .format("DD MMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VDivider class="mt-4 mb-4" />

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>สถานประกอบการ : </span>
                    <span>
                      {{ formActive.company_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ชื่อ-สกุล ผู้ประสานงาน : </span>
                    <span>
                      {{ formActive.co_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ตำแหน่งผู้ประสานงาน : </span>
                    <span>
                      {{ formActive.co_position }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>เบอร์โทรศัพท์ : </span>
                    <span>
                      {{ formActive.co_tel }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>email : </span>
                    <span>
                      {{ formActive.co_email }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ชื่อ-สกุลผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                    <span>
                      {{ formActive.request_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ตำแหน่งผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                    <span>
                      {{ formActive.request_position }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>นามบัตร : </span>
                    <span>
                      <a
                        :href="formActive.namecard_file"
                        target="_blank"
                      >
                        <VImg
                          v-if="formActive.namecard_file_type != 'pdf'"
                          :src="formActive.namecard_file"
                          style="max-width: 300px"
                          class="mt-2"
                        />

                        <a
                          v-if="formActive.namecard_file_type == 'pdf'"
                          :href="formActive.namecard_file"
                          target="_blank"
                        ><span>
                          <VIcon
                            size="16"
                            icon="tabler-file"
                            style="opacity: 1"
                            class="mr-1"
                          />นามบัตร</span>
                        </a>
                      </a>
                    </span>
                  </VCol>

                  <VDivider class="mt-6 mb-6" />

                  <VCol
                    cols="12"
                    md="6"
                    class="text-error"
                  >
                    <VRow>
                      <VCol
                        cols="12"
                        md="12"
                      >
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>

                    <VRow
                      v-for="(rl, index) in formActive.reject_log"
                      :key="index"
                    >
                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="4"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          วันที่ :
                        </h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="8"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          ผู้ตรวจ :
                        </h4>
                        <span>
                          {{ reject_status_show(rl.reject_status_id) }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="12"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          รายละเอียด :
                        </h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="12"
                      >
                        <hr style="border: solid #eee 1px">
                      </VCol>
                    </VRow>
                  </VCol>

                  <VDivider class="mt-6 mb-6" />
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่อาจารย์ที่ปรึกษาอนุมัติ : </span>
                    <span>
                      {{
                        formActive.advisor_verified_at
                          ? dayjs(formActive.advisor_verified_at)
                            .locale("th")
                            .format("DD MMM BBBB")
                          : "-"
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ประธานอาจารย์นิเทศอนุมัติ : </span>
                    <span>
                      {{
                        formActive.chairman_approved_at
                          ? dayjs(formActive.chairman_approved_at)
                            .locale("th")
                            .format("DD MMM BBBB")
                          : "-"
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่คณะอนุมัติ : </span>
                    <span>
                      {{
                        formActive.faculty_confirmed_at
                          ? dayjs(formActive.faculty_confirmed_at)
                            .locale("th")
                            .format("DD MMM BBBB")
                          : "-"
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  />
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip
                        label
                        :color="form_statuses[formActive.status_id]"
                      >
                        {{
                          statusShow(
                            formActive.status_id,
                            formActive.request_document_date,
                            formActive.confirm_response_at
                          )
                        }}</VChip>
                    </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>หนังสือขอความอนุเคราะห์ : </span>
                    <span>
                      {{
                        formActive.request_document_date == null
                          ? "-"
                          : dayjs(formActive.request_document_date)
                            .locale("th")
                            .format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ต้องตอบรับเอกสาร : </span>
                    <span>{{
                      formActive.max_response_date == null
                        ? "-"
                        : dayjs(formActive.max_response_date)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ไฟล์หนังสือตอบกลับ : </span>
                    <a
                      v-if="formActive.response_document_file"
                      :href="formActive.response_document_file"
                      target="_blank"
                    ><span>
                      <VIcon
                        size="16"
                        icon="tabler-file"
                        style="opacity: 1"
                        class="mr-1"
                      />เอกสาร</span>
                    </a>
                    <span v-else>-</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ส่งหนังสือตอบกลับ : </span>
                    <span>{{
                      formActive.response_send_at == null
                        ? "-"
                        : dayjs(formActive.response_send_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ผลการตอบกลับ : </span>
                    <span>{{
                      formActive.response_result == null
                        ? "-"
                        : statusShow(formActive.response_result)
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>จังหวัดที่ตอบรับสหกิจศึกษา : </span>
                    <span>{{
                      getProvince(formActive.response_province_id)
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ตรวจสอบหนังสือตอบกลับ : </span>
                    <span>{{
                      formActive.confirm_response_at == null
                        ? "-"
                        : dayjs(formActive.confirm_response_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>หนังสือส่งตัว : </span>
                    <span>
                      {{
                        formActive.send_document_date == null
                          ? "-"
                          : dayjs(formActive.send_document_date)
                            .locale("th")
                            .format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VDivider />

                  <VCol
                    cols="12"
                    md="6"
                    class="text-error"
                  >
                    <VRow>
                      <VCol
                        cols="12"
                        md="12"
                      >
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>
                    <VRow
                      v-for="(rl, index) in formActive.reject_log"
                      :key="index"
                    >
                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="4"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          วันที่ :
                        </h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span>
                      </VCol>
                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="12"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          รายละเอียด :
                        </h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="12"
                      >
                        <hr style="border: solid #eee 1px">
                      </VCol>
                    </VRow>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  />
                </VRow>
              </VWindowItem>
              <!--  -->
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ไฟล์แผนการปฏิบัติงาน : </span>
                    <a
                      v-if="formActive.plan_document_file"
                      :href="formActive.plan_document_file"
                      target="_blank"
                    ><span>
                      <VIcon
                        size="16"
                        icon="tabler-file"
                        style="opacity: 1"
                        class="mr-1"
                      />เอกสาร</span>
                    </a>
                    <span v-else>-</span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ส่งแผน : </span>
                    <span>{{
                      formActive.plan_send_at == null
                        ? "-"
                        : dayjs(formActive.plan_send_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่อนุมัติแผน : </span>
                    <span>{{
                      formActive.plan_accept_at == null
                        ? "-"
                        : dayjs(formActive.plan_accept_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <hr>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>ที่อยู่ที่ปฏิบัติงาน : </span>
                    <span>{{ formActive.workplace_address }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>จังหวัด : </span>
                    <span>{{
                      getProvince(formActive.workplace_province_id)
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>อำเภอ/เขต : </span>
                    <span>{{ getAmphur(formActive.workplace_amphur_id) }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ตำบล/แขวง : </span>
                    <span>{{ getTumbol(formActive.workplace_tumbol_id) }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ลิงค์แผนที่ : </span>
                    <a
                      v-if="formActive.workplace_googlemap_url"
                      :href="formActive.workplace_googlemap_url"
                      target="_blank"
                    >
                      <span>
                        <VIcon
                          size="16"
                          icon="tabler-map-pin"
                          style="opacity: 1"
                          class="mr-1"
                        />Map</span>
                    </a>
                    <span v-else>-</span>
                  </VCol>

                  <VDivider />

                  <VDivider />

                  <VCol
                    cols="12"
                    md="6"
                    class="text-error"
                  >
                    <VRow>
                      <VCol
                        cols="12"
                        md="12"
                      >
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>
                    <VRow
                      v-for="(rl, index) in formActive.reject_log"
                      :key="index"
                    >
                      <VCol
                        v-if="rl.reject_status_id > 4"
                        cols="12"
                        md="4"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          วันที่ :
                        </h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span>
                      </VCol>
                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="12"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          รายละเอียด :
                        </h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="12"
                      >
                        <hr style="border: solid #eee 1px">
                      </VCol>
                    </VRow>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  />
                </VRow>
              </VWindowItem>
            </VWindow>

            <div class="d-flex justify-space-between mt-8">
              <VBtn
                color="secondary"
                variant="tonal"
                :disabled="currentStep === 0"
                @click="currentStep--"
              >
                <VIcon
                  icon="tabler-chevron-left"
                  start
                  class="flip-in-rtl"
                />
                Previous
              </VBtn>
              <VBtn
                v-if="formSteps.length - 1 !== currentStep"
                @click="currentStep++"
              >
                Next
                <VIcon
                  icon="tabler-chevron-right"
                  end
                  class="flip-in-rtl"
                />
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!--  Old -->
      <VCol
        v-for="(it, index) in items"
        :key="index"
        cols="12"
        md="12"
      >
        <VExpansionPanels v-if="it.active != 1">
          <VExpansionPanel :title="`ครั้งที่ ${items.length - index} (ยกเลิก)`">
            <VExpansionPanelText class="pa-5">
              <VRow>
                <VCol
                  cols="12"
                  md="12"
                >
                  <span>สถานะฟอร์ม : </span>
                  <span>
                    <VChip
                      label
                      :color="form_statuses[it.status_id]"
                    >
                      {{
                        statusShow(
                          it.status_id,
                          it.request_document_date,
                          it.confirm_response_at
                        )
                      }}</VChip>
                  </span>
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ปีการศึกษา : </span>
                  <span>
                    {{ it.semester_name }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>อาจารย์นิเทศ : </span>
                  <span>
                    {{ it.supervision_name }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(it.start_date).locale("th").format("DD MMM BBBB")
                    }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                  <span>
                    {{ dayjs(it.end_date).locale("th").format("DD MMM BBBB") }}
                  </span>
                </VCol>

                <VDivider class="mt-4 mb-4" />

                <VCol
                  cols="12"
                  md="12"
                >
                  <span>สถานประกอบการ : </span>
                  <span>
                    {{ it.company_name }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ชื่อ-สกุล ผู้ประสานงาน : </span>
                  <span>
                    {{ it.co_name }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ตำแหน่งผู้ประสานงาน : </span>
                  <span>
                    {{ it.co_position }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>เบอร์โทรศัพท์ : </span>
                  <span>
                    {{ it.co_tel }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>email : </span>
                  <span>
                    {{ it.co_email }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ชื่อ-สกุลผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                  <span>
                    {{ it.request_name }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ตำแหน่งผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                  <span>
                    {{ it.request_position }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="12"
                >
                  <span>นามบัตร : </span>
                  <span>
                    <a
                      :href="it.namecard_file"
                      target="_blank"
                    >
                      <VImg
                        :src="it.namecard_file"
                        style="max-width: 300px"
                        class="mt-2"
                      />
                    </a>
                  </span>
                </VCol>

                <VDivider class="mt-6 mb-6" />

                <VCol
                  cols="12"
                  md="6"
                >
                  <h4>คุณสมบัติผู้สมัครโครงการสหกิจศึกษา</h4>
                  <VList :items="qualifications" />
                  <!-- <VCheck  -->
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                  class="text-error"
                >
                  <VRow>
                    <VCol
                      cols="12"
                      md="12"
                    >
                      <h4>Remark</h4>
                    </VCol>
                  </VRow>

                  <VRow
                    v-for="(rl, index) in it.reject_log"
                    :key="index"
                  >
                    <VCol
                      v-if="rl.reject_status_id < 4"
                      cols="12"
                      md="4"
                    >
                      <h4 class="mb-0 d-inline mr-1 text-error">
                        วันที่ :
                      </h4>
                      <span>
                        {{
                          dayjs(rl.created_at).locale("th").format("DD MMM BB")
                        }}</span>
                    </VCol>

                    <VCol
                      v-if="rl.reject_status_id < 4"
                      cols="12"
                      md="8"
                    >
                      <h4 class="mb-0 d-inline mr-1 text-error">
                        ผู้ตรวจ :
                      </h4>
                      <span>
                        {{ reject_status_show(rl.reject_status_id) }}</span>
                    </VCol>

                    <VCol
                      v-if="rl.reject_status_id < 4"
                      cols="12"
                      md="12"
                    >
                      <h4 class="mb-0 d-inline mr-1 text-error">
                        รายละเอียด :
                      </h4>
                      <span> {{ rl.comment }}</span>
                    </VCol>

                    <VCol
                      v-if="rl.reject_status_id < 4"
                      cols="12"
                      md="12"
                    >
                      <hr style="border: solid #eee 1px">
                    </VCol>
                  </VRow>
                </VCol>

                <VDivider class="mt-6 mb-6" />
                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่อาจารย์ที่ปรึกษาอนุมัติ : </span>
                  <span>
                    {{
                      it.advisor_verified_at
                        ? dayjs(it.advisor_verified_at)
                          .locale("th")
                          .format("DD MMM BBBB")
                        : "-"
                    }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่ประธานอาจารย์นิเทศอนุมัติ : </span>
                  <span>
                    {{
                      it.chairman_approved_at
                        ? dayjs(it.chairman_approved_at)
                          .locale("th")
                          .format("DD MMM BBBB")
                        : "-"
                    }}
                  </span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่คณะอนุมัติ : </span>
                  <span>
                    {{
                      it.faculty_confirmed_at
                        ? dayjs(it.faculty_confirmed_at)
                          .locale("th")
                          .format("DD MMM BBBB")
                        : "-"
                    }}
                  </span>
                </VCol>

                <VDivider />

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>หนังสือขอความอนุเคราะห์ : </span>
                  <span>
                    {{
                      it.request_document_date == null
                        ? "-"
                        : dayjs(it.request_document_date)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}
                  </span>
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่ต้องตอบรับเอกสาร : </span>
                  <span>{{
                    it.max_response_date == null
                      ? "-"
                      : dayjs(it.max_response_date)
                        .locale("th")
                        .format("DD MMMM BBBB")
                  }}</span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ไฟล์หนังสือตอบกลับ : </span>
                  <a
                    v-if="it.response_document_file"
                    :href="it.response_document_file"
                    target="_blank"
                  ><span>
                    <VIcon
                      size="16"
                      icon="tabler-file"
                      style="opacity: 1"
                      class="mr-1"
                    />เอกสาร</span>
                  </a>
                  <span v-else>-</span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่ส่งหนังสือตอบกลับ : </span>
                  <span>{{
                    it.response_send_at == null
                      ? "-"
                      : dayjs(it.response_send_at)
                        .locale("th")
                        .format("DD MMMM BBBB")
                  }}</span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>ผลการตอบกลับ : </span>
                  <span>{{
                    it.response_result == null
                      ? "-"
                      : statusShow(it.response_result)
                  }}</span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>จังหวัดที่ตอบรับสหกิจศึกษา : </span>
                  <span>{{ getProvince(it.response_province_id) }}</span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>วันที่ตรวจสอบหนังสือตอบกลับ : </span>
                  <span>{{
                    it.confirm_response_at == null
                      ? "-"
                      : dayjs(it.confirm_response_at)
                        .locale("th")
                        .format("DD MMMM BBBB")
                  }}</span>
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <span>หนังสือส่งตัว : </span>
                  <span>
                    {{
                      it.send_document_date == null
                        ? "-"
                        : dayjs(it.send_document_date)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}
                  </span>
                </VCol>

                <VDivider />

                <VCol
                  cols="12"
                  md="12"
                  class="text-error"
                >
                  <VRow>
                    <VCol
                      cols="12"
                      md="12"
                    >
                      <h4>Remark</h4>
                    </VCol>
                  </VRow>
                  <VRow
                    v-for="(rl, index) in it.reject_log"
                    :key="index"
                  >
                    <VCol
                      v-if="rl.reject_status_id > 3"
                      cols="12"
                      md="12"
                    >
                      <h4 class="mb-0 d-inline mr-1 text-error">
                        วันที่ :
                      </h4>
                      <span>
                        {{
                          dayjs(rl.created_at).locale("th").format("DD MMM BB")
                        }}</span>
                    </VCol>
                    <VCol
                      v-if="rl.reject_status_id > 3"
                      cols="12"
                      md="12"
                    >
                      <h4 class="mb-0 d-inline mr-1 text-error">
                        รายละเอียด :
                      </h4>
                      <span> {{ rl.comment }}</span>
                    </VCol>

                    <VCol
                      v-if="rl.reject_status_id > 3"
                      cols="12"
                      md="12"
                    >
                      <hr style="border: solid #eee 1px">
                    </VCol>
                  </VRow>
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>
        </VExpansionPanels>
      </VCol>
    </VRow>

    <VSnackbar
      v-model="isSnackbarVisible"
      location="top end"
      :color="snackbarColor"
    >
      {{ snackbarText }}
      <template #actions>
        <VBtn
          color="error"
          @click="isSnackbarVisible = false"
        >
          Close
        </VBtn>
      </template>
    </VSnackbar>

    <VOverlay
      v-model="isOverlay"
      contained
      persistent
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
  </div>
</template>

<style lang="scss">
.checkout-stepper {
  .stepper-icon-step {
    .step-wrapper + svg {
      margin-inline: 3.5rem !important;
    }
  }
}

.swal2-container {
  z-index: 20001 !important;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
