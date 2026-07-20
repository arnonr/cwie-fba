<script setup>
import dayjs from "dayjs"
import "dayjs/locale/th"
import "sweetalert2/src/sweetalert2.scss"
import { useRoute, useRouter } from "vue-router"

////
import buddhistEra from "dayjs/plugin/buddhistEra"

// import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css"
import { useCwieDataStore } from "../useCwieDataStore"

import { form_statuses, statusShow } from "@/data-constant/data"

// const route = useRoute();
dayjs.extend(buddhistEra)

const route = useRoute()
const router = useRouter()
const cwieDataStore = useCwieDataStore()

let userData = JSON.parse(localStorage.getItem("userData"))

const student = ref({})
const item = ref({})

const rejectLog = ref({
  comment: "",
  reject_status_id: 2,
  form_id: null,
  user_id: userData.user_id,
})

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const orderBy = ref("id")
const order = ref("desc")
const isAdd = ref(true)
const isCheck = ref(true)
const isDialogVisible = ref(false)
const isDialogRejectVisible = ref(false)
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

const items = ref([])
const prependIcon = "tabler-arrow-big-right-filled"

const qualifications = [
  {
    title:
      "ผ่านรายวิชาเตรียมสหกิจศึกษา หรืออยู่ระหว่างเรียนวิชาเตรียมสหกิจศึกษา",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "ผ่านรายวิชาหมวดศึกษาทั่วไปและหมวดวิชาเฉพาะ ไม่น้อยกว่า 15, 60 หน่วยกิต ตามลำดับ",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "มีเกรดเฉลี่ยไม่ต่ำกว่า 2.00 นับถึงภาคการศึกษาสุดท้ายก่อนออกฝึกสหกิจศึกษา",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "ไม่อยู่ในระหว่างถูกพักการศึกษาในภาคการศึกษาที่เข้าร่วมโครงการสหกิจศึกษา",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title: "ไม่เคยถูกลงโทษทางวินัย หรือกระทำผิดกฏหมายอาญาร้ายแรง",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title: "ไม่เป็นโรคที่เป็นอุปสรรคต่อการปฏิบัติงานในสถานประกอบการ",
    props: {
      prependIcon: prependIcon,
    },
  },
]

const documents_certificate = ref([])

const isOverlay = ref(false)
const isFormValid = ref(false)
const refForm = ref()
const currentTab = ref(0)

const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  teachers: [],
  document_types: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
})

const fetchDocumentTypes = () => {
  cwieDataStore
    .fetchDocumentTypes({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.document_types = response.data.data.map(r => {
          return { title: r.name, value: r.id }
        })

        selectOptions.value.document_types =
          selectOptions.value.document_types.filter(d => {
            return d.value != 1
          })

        fetchStudent()

        isOverlay.value = false
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const fetchStudentDocuments = () => {
  cwieDataStore
    .fetchStudentDocuments({
      student_id: student.value.id,
    })
    .then(response => {
      if (response.status === 200) {
        const { data } = response.data

        let document = data.filter(d => {
          return d.document_type_id != 1
        })

        student.value.documents = student.value.documents.map(d => {
          let index = document.find(e => {
            return d.document_type_id == e.document_type_id
          })
          if (index) {
            d.id = index.id
            d.document_file_old = index.document_file
            d.document_file = []
          } else {
            isCheck.value = false
          }
          
          return d
        })

        // Cert
        let document_cert = data.filter(d => {
          return d.document_type_id == 1
        })

        if (document_cert.length > 0) {
          documents_certificate.value = []

          for (let index = 0; index < document_cert.length; index++) {
            let d = document_cert[index]
            documents_certificate.value.push({
              id: d.id,
              document_type_id: 1,
              document_name: d.document_name,
              student_id: d.student_id,
              document_file_old:
                d.document_file != null ? d.document_file : null,
              document_file: [],
            })
          }
        }

        isOverlay.value = false
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

// let userData = JSON.parse(localStorage.getItem("userData"));

const fetchStudent = () => {
  cwieDataStore
    .fetchStudents({
      id: route.params.id,
      includeAll: true,
    })
    .then(response => {
      if (response.data.message == "success") {
        const { data } = response.data

        student.value = { ...data[0] }

        student.value.faculty_name = student.value.faculty_name.replace(
          "คณะ",
          "",
        )

        student.value.major_name = student.value.major_name
          ? student.value.major_name.replace("สาขาวิชา", "")
          : ""

        // student.value.status = 1;

        student.value.documents = selectOptions.value.document_types.map(
          d => {
            return {
              id: null,
              document_file: null,
              document_file_old: ref(null),
              document_type_id: d.value,
              document_name: d.title,
              student_id: student.value.id,
            }
          },
        )

        if (
          student.value.id &&
          student.value.prefix_id &&
          student.value.firstname &&
          student.value.surname &&
          student.value.citizen_id &&
          student.value.address &&
          student.value.province_id &&
          student.value.amphur_id &&
          student.value.tumbol_id &&
          student.value.tel &&
          student.value.email &&
          student.value.faculty_id &&

          //   student.value.major_id &&
          student.value.class_year &&
          student.value.class_room &&
          student.value.advisor_id &&
          student.value.gpa &&
          student.value.contact1_name &&
          student.value.contact1_relation &&
          student.value.contact1_tel &&
          student.value.blood_group &&
          student.value.emergency_tel &&
          student.value.height &&
          student.value.weight
        ) {
        } else {
          isCheck.value = false
        }

        // if (isCheck == false) {
        //   router.push({
        //     name: "student-personal-data",
        //   });
        // }
        // currentStep.value = 2;
        // console.log(currentStep);
        fetchStudentDocuments()
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

const fetchForms = () => {
  cwieDataStore
    .fetchForms({
      student_id: route.params.id,

      //   student_id: student.value.id,
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      includeAll: true,
    })
    .then(async response => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data

        for (let i = 0; i < data.length; i++) {
          await cwieDataStore
            .fetchMajorHeads({
              semester_id: data[i].semester_id,
              major_id: student.value.major_id,
              active: 1,
              includeAll: true,
            })
            .then(res => {
              data[i].major_head_name = res.data.data[0].teacher_name
            })
        }
        items.value = data

        if (items.value.length != 0) {
          isAdd.value = false
          if (items.value[0].status_id == 6 || items.value[0].status_id == 8) {
            isAdd.value = true
          }
        }

        totalPage.value = response.data.totalPage
        totalItems.value = response.data.totalData
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const onRejectSubmit = () => {
  if (rejectLog.comment != "") {
    cwieDataStore
      .addRejectLog({
        ...rejectLog.value,
        active: 1,
      })
      .then(response => {
        if (response.data.message == "success") {
          localStorage.setItem("Rejected", 1)
          nextTick(() => {
            fetchStudent()
            fetchForms()
            snackbarText.value = "Rejected"
            snackbarColor.value = "success"
            isSnackbarVisible.value = true
            isOverlay.value = false
            isDialogRejectVisible.value = false
          })
        } else {
          isOverlay.value = false
          console.log("error")
        }
      })
      .catch(error => {
        console.error(error)
      })
  } else {
    snackbarText.value = "โปรดระบุเหตุผล"
    snackbarColor.value = "error"
    isSnackbarVisible.value = true
    isOverlay.value = false
  }
}

const fetchProvinces = () => {
  cwieDataStore
    .fetchProvinces({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.provinces = response.data.data.map(r => {
          return { title: r.name_th, value: r.province_id }
        })
        isOverlay.value = false
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const onSubmit = () => {
  cwieDataStore
    .approve({
      id: item.value.id,
      status_id: 4,
      chairman_approved_at: dayjs().format("YYYY-MM-DD"),
    })
    .then(response => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1)
        nextTick(() => {
          fetchStudent()
          fetchForms()
          snackbarText.value = "Approved"
          snackbarColor.value = "success"
          isSnackbarVisible.value = true
          isOverlay.value = false
          isDialogVisible.value = false
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

const responseProvinceName = response_province_id => {
  if (response_province_id) {
    let response_province_select = selectOptions.value.provinces.find(x => {
      return x.value == response_province_id
    })
    
    return response_province_select.title
  } else {
    return "-"
  }
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

onMounted(() => {
  window.scrollTo(0, 0)

  fetchProvinces()

  //   fetchTeachers();
  fetchDocumentTypes()
})
</script>

<template>
  <div>
    <VRow>
      <VCol
        cols="12"
        md="3"
      >
        <VCard
          title=""
          class="pa-3"
        >
          <VCardText>
            <VImg
              :src="student.photo_file"
              width="100"
              height="120"
              class="mx-auto"
            />
          </VCardText>

          <VCardText>
            <span class="font-weight-bold">ชื่อ : </span>
            <span>{{ student.prefix_name }}{{ student.firstname }}
              {{ student.surname }}</span>
          </VCardText>
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">รหัสนักศึกษา : </span>
            <span>{{ student.student_code }}</span>
          </VCardText>
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">สาขาวิชา : </span>
            <span>{{ student.major_name }}</span>
          </VCardText>
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">ชั้นปี : </span>
            <span>{{ student.class_year }}</span>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="9"
      >
        <VCard class="pa-5">
          <VTabs v-model="currentTab">
            <VTab>
              <VIcon
                size="16"
                icon="tabler-user"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลทั่วไป
            </VTab>
            <VTab>
              <VIcon
                size="16"
                icon="tabler-heart"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลสุขภาพ
            </VTab>
            <VTab>
              <VIcon
                size="16"
                icon="tabler-books"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลเอกสาร
            </VTab>
          </VTabs>

          <VCardText>
            <VWindow v-model="currentTab">
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-book"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ข้อมูลการศึกษา</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="3"
                  >
                    <span>ห้อง : </span>
                    <span>{{ student.class_room }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>อาจารย์ที่ปรึกษา : </span>
                    <span>{{ student.advisor_name }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <span>เกรดเฉลี่ย : </span>
                    <span>{{ student.gpa }} </span>
                  </VCol>

                  <VDivider />
                  <!-- address -->
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-map-pin"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ข้อมูลที่อยู่</span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>ที่อยู่ปัจจุบัน : </span>
                    <span>{{ student.address }} </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <span>จังหวัด : </span>
                    <span>{{ student.province_name }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="4"
                  >
                    <span>อำเภอ/เขต : </span>
                    <span>{{ student.amphur_name }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="4"
                  >
                    <span>ตำบล/แขวง : </span>
                    <span>{{ student.tumbol_name }} </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>เบอร์โทรศัพท์ : </span>
                    <span>{{ student.tel }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>เมล : </span>
                    <span>{{ student.email }} </span>
                  </VCol>
                  <VDivider />
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-users"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ผู้ที่ติดต่อได้</span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>บุคคลที่ติดต่อได้1 : </span>
                    <span>{{ student.contact1_name }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <span>ความสัมพันธ์ : </span>
                    <span>{{ student.contact1_relation }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <span>โทร : </span>
                    <span>{{ student.contact1_tel }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>บุคคลที่ติดต่อได้2 : </span>
                    <span>{{ student.contact2_name }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <span>ความสัมพันธ์ : </span>
                    <span>{{ student.contact2_relation }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <span>โทร : </span>
                    <span>{{ student.contact2_tel }} </span>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-heart"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ข้อมูลสุขภาพ</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <span>กลุ่มเลือด : </span>
                    <span>{{ student.blood_group }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="4"
                  >
                    <span>ส่วนสูง (ซม.) : </span>
                    <span>{{ student.height }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="4"
                  >
                    <span>น้ำหนัก (กก.) : </span>
                    <span>{{ student.weight }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>เบอร์โทรฉุกเฉิน : </span>
                    <span>{{ student.emergency_tel }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>โรคประจำตัว : </span>
                    <span>{{ student.congenital_disease }} </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>ประวัติการแพ้ยา : </span>
                    <span>{{ student.drug_allergy }} </span>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-books"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ประกาศนียบัตร</span>
                  </VCol>
                  <VCol
                    v-for="(d, index) in documents_certificate"
                    :key="index"
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">{{ d.document_name }} :
                    </span>
                    <a
                      :href="
                        d.document_file_old != null ? d.document_file_old : '#'
                      "
                      target="_blank"
                    ><span>
                      <VIcon
                        size="16"
                        icon="tabler-file"
                        style="opacity: 1"
                        class="mr-1"
                      />เอกสาร</span>
                    </a>
                  </VCol>
                  <VDivider />
                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-books"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      เอกสาร</span>
                  </VCol>

                  <VCol
                    v-for="(d, index) in student.documents"
                    :key="index"
                    cols="12"
                    md="12"
                  >
                    <span class="font-weight-bold">{{ d.document_name }} :
                    </span>
                    <a
                      :href="
                        d.document_file_old != null ? d.document_file_old : '#'
                      "
                      target="_blank"
                    ><span>
                      <VIcon
                        size="16"
                        icon="tabler-file"
                        style="opacity: 1"
                        class="mr-1"
                      />เอกสาร</span>
                    </a>
                    <!--
                      <iframe
                      v-if="d.document_file_old != null"
                      :src="d.document_file_old"
                      style="width: 100%; height: 500px"
                      ></iframe>

                      <span v-else> <br />- </span> 
                    -->
                    <!--
                      <vue-pdf-app
                      :id="'pdf' + index"
                      style="height: 500px"
                      :pdf="d.document_file_old"
                      ></vue-pdf-app> 
                    -->
                  </VCol>
                </VRow>
              </VWindowItem>
            </VWindow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        v-for="(it, index) in items"
        :key="index"
        cols="12"
        md="12"
      >
        <VCard class="pa-5">
          <VCardText>
            <h3>ครั้งที่ {{ items.length - index }}</h3>
          </VCardText>
          <VCardText>
            <AppStepper
              v-model:current-step="currentStep"
              class="checkout-stepper"
              :items="formSteps"
              :is-active-step-valid="true"
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
                  <!--
                    <VCol cols="12" md="12" class="d-flex">
                    <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                    <h4 class="pt-1 pl-1">ข้อมูลทั่วไป</h4>
                    </VCol> 
                  -->

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
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
                    <!-- <VCol cols="12" md="3"> -->
                    <span>ปีการศึกษา : </span>
                    <span>
                      {{ it.semester_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ประธานอาจารย์นิเทศประจำสาขา : </span>
                    <span>
                      {{ it.major_head_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
                    <span>อาจารย์นิเทศ : </span>
                    <span>
                      {{ it.supervision_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
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
                      {{
                        dayjs(it.end_date).locale("th").format("DD MMM BBBB")
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

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  >
                    <VBtn
                      color="error"
                      :disabled="it.status_id != 3 || index != 0"
                      @click="
                        () => {
                          rejectLog.form_id = it.id;
                          isDialogRejectVisible = true;
                        }
                      "
                    >
                      <VIcon
                        size="16"
                        icon="tabler-edit"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ส่งกลับให้แก้ไข
                    </VBtn>

                    <VBtn
                      class="ml-2"
                      color="success"
                      :disabled="it.status_id != 3 || index != 0"
                      @click="
                        () => {
                          item = it;
                          isDialogVisible = true;
                        }
                      "
                    >
                      <VIcon
                        size="16"
                        icon="tabler-file-description"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      อนุมัติ
                    </VBtn>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
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
                    <span>{{
                      responseProvinceName(it.response_province_id)
                    }}</span>
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
                        md="8"
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
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
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
                    <span>ไฟล์แผนการปฏิบัติงาน : </span>
                    <a
                      v-if="it.plan_document_file"
                      :href="it.plan_document_file"
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
                      it.plan_send_at == null
                        ? "-"
                        : dayjs(it.plan_send_at)
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
                      it.plan_accept_at == null
                        ? "-"
                        : dayjs(it.plan_accept_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>ที่อยู่ที่ปฏิบัติงาน : </span>
                    <span>{{ it.workplace_address }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>จังหวัด : </span>
                    <span>{{ it.workplace_province_id }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>อำเภอ/เขต : </span>
                    <span>{{ it.workplace_amphur_id }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ตำบล/แขวง : </span>
                    <span>{{ it.workplace_tumbol_id }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ลิงค์แผนที่ : </span>
                    <a
                      v-if="it.workplace_googlemap_url"
                      :href="it.workplace_googlemap_url"
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

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ภาพแผนที่ : </span>
                    <VImg
                      v-if="it.workplace_googlemap_file"
                      :src="it.workplace_googlemap_file"
                    />
                    <span v-else>-</span>
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
                      v-for="(rl, index) in it.reject_log"
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
                        v-if="rl.reject_status_id > 4"
                        cols="12"
                        md="8"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          รายละเอียด :
                        </h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>
                      <VCol
                        v-if="rl.reject_status_id > 4"
                        cols="12"
                        md="12"
                      >
                        <hr style="border: solid #eee 1px">
                      </VCol>
                    </VRow>
                  </VCol>
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

    <!-- Dialog -->
    <VDialog
      v-model="isDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Are You Sure?">
        <VCardText> ยืนยันการอนุมัติ </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="error"
            @click="isDialogVisible = !isDialogVisible"
          >
            Cancel
          </VBtn>
          <VBtn
            color="success"
            @click="onSubmit"
          >
            Approve
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isDialogRejectVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogRejectVisible = !isDialogRejectVisible" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มส่งข้อมูลกลับให้แก้ไข">
        <VCardText>
          <VCol
            cols="12"
            md="12"
            class="align-items-center"
          >
            <label
              class="v-label font-weight-bold"
              for="comment"
            >ระบุเหตุผล :
            </label>
            <AppTextarea
              id="comment"
              v-model="rejectLog.comment"
              rows="5"
              persistent-placeholder
            />
          </VCol>
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="error"
            @click="isDialogRejectVisible = !isDialogRejectVisible"
          >
            Cancel
          </VBtn>
          <VBtn
            color="success"
            @click="onRejectSubmit"
          >
            Reject
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
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
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
