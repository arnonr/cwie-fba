<script setup>
import VisitForm from "@/components/visit/VisitForm.vue"
import VisitView from "@/components/visit/VisitView.vue"
import {
  class_rooms,
  class_years,
  statuses,
  form_statuses,
  statusShow,
  visit_status,
} from "@/data-constant/data"
import { useStudentListStore } from "./useStudentListStore"
import axios from "@axios"
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"

const props = defineProps(["user_type"])

dayjs.extend(buddhistEra)

const studentListStore = useStudentListStore()

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const isDialogFormVisitStudent = ref(false)
const isDialogViewVisitStudent = ref(false)

const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")

const checkSemester = ref(true)
const items = ref([])
const provinces = ref([])
const view_student_id = ref(null)
const major = ref([]) // สำหรับประธานอาจารย์นิเทศ

const advancedSearch = reactive({
  semester_id: "",
  status_id: "",
  student_code: "",
  firstname: "",
  surname: "",
  major_id: "",
  class_year: "",
  class_room: "",
  advisor_id: "",
  supervision_id: "",
  company_name: "",
  province_id: "",
  approve_status: "",
  plan_status: "",
  visit_status: "",
})

const selectOptions = ref({
  perPage: [
    { title: "20", value: 20 },
    { title: "40", value: 40 },
    { title: "60", value: 60 },
  ],
  teachers: [],
  semesters: [],
  companies: [],
  statuses: statuses,
  class_years: class_years,
  class_rooms: class_rooms,
  approve_statuses: [],
  visit_statuses: [],
  plan_statuses: [
    { title: "รออนุมัติแผน", value: 1 },
    { title: "อนุมัติแผนเรียบร้อย", value: 2 },
  ],
})

if (props.user_type == "major-head") {
  const teacherData = JSON.parse(localStorage.getItem("teacherData"))

  selectOptions.value.visit_statuses = [
    { title: "รอประธานอาจารย์นิเทศอนุมัติ", value: 11 },
    { title: "ประธานอาจารย์นิเทศอนุมัติเรียบร้อย", value: 2 },
  ]

  const fetchMajorHeads = () => {
    studentListStore
      .fetchMajorHeads({
        teacher_id: teacherData.id,
        perPage: 100,
      })
      .then(response => {
        if (response.status === 200) {
          let semester = response.data.data.map(r => {
            return r.semester_id
          })

          major.value = response.data.data.map(r => {
            return r.major_id
          })

          if (semester.length == 0) {
            checkSemester.value = false
          }
          fetchSemesters()
          fetchItems()
        } else {
          console.log("error")
        }
      })
      .catch(error => {
        console.error(error)
        isOverlay.value = false
      })
  }

  fetchMajorHeads()
} else if (props.user_type == "supervisor") {
  selectOptions.value.approve_statuses = []

  //
} else if (props.user_type == "chairman") {
  selectOptions.value.visit_statuses = [
    { title: "รอประธานบริหารอนุมัติ", value: 21 },
    { title: "ประธานบริหารอนุมัติเรียบร้อย", value: 3 },
  ]
} else {
  selectOptions.value.approve_statuses = [
    { title: "รอคณะอนุมัติ", value: 5 },
    { title: "คณะอนุมัติเรียบร้อย", value: 6 },
  ]

  selectOptions.value.visit_statuses = [
    { title: "รอออกหนังสือ", value: 31 },
    { title: "ออกหนังสือแล้ว", value: 4 },
  ]
}

const fetchSemesters = () => {
  let search = {}

  if (checkSemester.value == false) {
    return
  }
  if (props.user_type == "chairman") {
    search["chairman_id"] = JSON.parse(localStorage.getItem("teacherData")).id
  }
  studentListStore
    .fetchSemesters(search)
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.semesters = response.data.data.map(r => {
          if (r.is_current == 1) {
            advancedSearch.semester_id = r.id
          }
          
          return {
            title: r.term + "/" + r.semester_year + " รอบที่" + r.round_no,
            value: r.id,
            start_date: r.start_date,
            end_date: r.end_date,
          }
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

if (props.user_type != "major-head") {
  fetchSemesters()
}

// เฉพาะเมนูของคณะและประธานบริหาร
const fetchTeachers = () => {
  studentListStore
    .fetchTeachers({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.teachers = response.data.data.map(r => {
          return {
            title: r.prefix + r.firstname + " " + r.surname,
            value: r.id,
          }
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

fetchTeachers()

// เฉพาะเมนูของคณะและประธานบริหาร
const fetchMajors = () => {
  studentListStore
    .fetchMajors({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.majors = response.data.data.map(r => {
          return {
            title: r.name_th,
            value: r.id,
          }
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

fetchMajors()

// 👉 Fetching
const fetchProvince = async () => {
  let res = await axios.get("/province", {
    validateStatus: () => true,
  })
  provinces.value = res.data.data
}

fetchProvince()

const fetchItems = () => {
  let search = {
    ...advancedSearch,
  }

  if (advancedSearch.semester_id != "") {
    if (props.user_type == "major-head") {
      search["major_id_array"] = major.value
    }

    if (props.user_type == "supervisor") {
      search["supervision_id"] = JSON.parse(
        localStorage.getItem("teacherData"),
      ).id
    }

    studentListStore
      .fetchListStudents({
        perPage: rowPerPage.value,
        currentPage: currentPage.value,
        orderBy: orderBy.value,
        order: order.value,
        ...search,
        includeAll: true,
        includeForm: true,
        includeVisit: true,
      })
      .then(response => {
        if (response.status === 200) {
          items.value = response.data.data
          totalPage.value = response.data.totalPage
          totalItems.value = response.data.totalData
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
}

const getProvince = province_id => {
  if (province_id == null) return ""
  let res = provinces.value.find(e => {
    return e.province_id == province_id
  })
  
  return res.name_th
}

watchEffect(fetchItems)

watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value
})

onMounted(() => {
  window.scrollTo(0, 0)
})

const refreshData = () => {
  isDialogFormVisitStudent.value = false
  fetchItems()
}
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="ข้อมูลนักศึกษา">
      <VCardItem>
        <VRow class="mt-1 mb-1">
          <!-- Search -->
          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="rowPerPage"
              label="จำนวนรายการ/page"
              density="compact"
              variant="outlined"
              :items="selectOptions.perPage"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="8"
          >
            <VSelect
              v-model="advancedSearch.semester_id"
              label="ปีการศึกษา"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.semesters"
            />
          </VCol>
          <VSpacer />
          <VCol
            v-if="props.user_type == 'staff'"
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="advancedSearch.status_id"
              label="สถานะ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.statuses"
            />
          </VCol>
          <VSpacer />

          <VCol
            v-if="props.user_type == 'staff' || props.user_type == 'supervisor'"
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="advancedSearch.plan_status"
              label="สถานะแผนการปฏิบัติงาน"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.plan_statuses"
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="advancedSearch.visit_status"
              label="สถานะการขอออกนิเทศ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.visit_statuses"
            />
          </VCol>
          <VCol
            cols="12"
            sm="4"
          >
            <VTextField
              v-model="advancedSearch.student_code"
              label="รหัสนักศึกษา"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            sm="4"
          >
            <VTextField
              v-model="advancedSearch.firstname"
              label="ชื่อ"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            sm="4"
          >
            <VTextField
              v-model="advancedSearch.surname"
              label="นามสกุล"
              density="compact"
            />
          </VCol>
          <VCol
            v-if="props.user_type == 'staff' || props.user_type == 'chairman'"
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="advancedSearch.major_id"
              label="สาขาวิชา"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.majors"
            />
          </VCol>

          <VCol
            v-if="props.user_type == 'staff' || props.user_type == 'chairman'"
            cols="12"
            sm="6"
          >
            <AppAutocomplete
              v-model="advancedSearch.advisor_id"
              :items="selectOptions.teachers"
              placeholder="อาจารย์ที่ปรึกษา"
              label="อาจารย์ที่ปรึกษา"
              density="compact"
              variant="outlined"
              clearable
            />
          </VCol>

          <VCol
            v-if="props.user_type == 'staff' || props.user_type == 'chairman'"
            cols="12"
            sm="6"
          >
            <AppAutocomplete
              v-model="advancedSearch.supervision_id"
              :items="selectOptions.teachers"
              placeholder="อาจารย์นิเทศ"
              label="อาจารย์นิเทศ"
              density="compact"
              variant="outlined"
              clearable
            />
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <VTextField
              v-model="advancedSearch.company_name"
              label="สถานประกอบการ"
              density="compact"
            />
          </VCol>

          <!-- Table -->
          <VCol
            cols="12"
            sm="12"
          >
            <VTable
              v-if="
                selectOptions.semesters.length != 0 ||
                  props.user_type == 'staff'
              "
              class=""
            >
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <!-- <th scope="col" class="font-weight-bold">รหัสนักศึกษา</th> -->
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    ชื่อ-นามสกุล
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    สถานประกอบการ
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    จังหวัด
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    สถานะ
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    สถานะใบขอออกนิเทศ
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    วันที่ออกนิเทศ
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    จัดการ
                  </th>
                </tr>
              </thead>
              <!-- 👉 table body -->
              <tbody>
                <tr
                  v-for="it in items"
                  :key="it.id"
                  style="height: 3.75rem"
                >
                  <td class="text-center">
                    {{ it.firstname + " " + it.surname }}
                  </td>
                  <td class="text-center">
                    {{ it.company_name }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ getProvince(it.workplace_province_id) }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
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
                      }}
                    </VChip>
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    <span v-if="it.visit_status">
                      <VChip
                        v-if="it.visit_reject_status_id == null"
                        label
                        :color="visit_status[it.visit_status].color"
                      >{{ visit_status[it.visit_status].title }}</VChip>
                      <VChip
                        v-else
                        label
                        color="error"
                      >รอแก้ไขข้อมูล</VChip>
                    </span>
                  </td>

                  <td class="text-center">
                    {{
                      it.visit_date
                        ? dayjs(it.visit_date)
                          .locale("th")
                          .format("DD MMM BB") +
                          " " +
                          it.visit_time.substring(0, it.visit_time.length - 3) +
                          " น."
                        : ""
                    }}
                  </td>

                  <td class="text-center">
                    <VBtn
                      v-if="it.visit_id != null"
                      color="success"
                      class="ml-2"
                      @click="
                        () => {
                          view_student_id = it.id;
                          isDialogViewVisitStudent = true;
                        }
                      "
                    >
                      ดูข้อมูล
                    </VBtn>
                  </td>
                </tr>
              </tbody>

              <!-- 👉 table footer  -->
              <tfoot v-show="!items.length">
                <tr>
                  <td
                    colspan="7"
                    class="text-center"
                  >
                    No data available
                  </td>
                </tr>
              </tfoot>
              <tfoot v-show="items.length" />
            </VTable>
          </VCol>

          <VCol
            cols="12"
            sm="12"
          >
            <span class="text-sm text-disabled">
              Showing {{ currentPage }} to {{ totalPage }} of
              {{ totalItems }} entries
            </span>
            <VPagination
              v-model="currentPage"
              size="small"
              :total-visible="5"
              :length="totalPage"
            />
          </VCol>
        </VRow>
      </VCardItem>
    </VCard>

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
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>

    <VDialog
      v-model="isDialogViewVisitStudent"
      persistent
      class="v-dialog-lg"
      style="z-index: 2000"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogViewVisitStudent = !isDialogViewVisitStudent" />

      <!-- Dialog Content -->
      <VCard title="ข้อมูลขอออกนิเทศ">
        <VCardText>
          <VisitView
            :user_type="props.user_type"
            :student_id="view_student_id"
            @refresh-data="refreshData"
            @close="isDialogViewVisitStudent = false"
          />
        </VCardText>
      </VCard>
    </VDialog>

    <!--
      <VDialog
      v-model="isDialogFormVisitStudent"
      persistent
      class="v-dialog-lg"
      style="z-index: 2000"
      >
      <DialogCloseBtn
      @click="isDialogFormVisitStudent = !isDialogFormVisitStudent"
      />

      <VCard title="แบบฟอร์มขอออกนิเทศ">
      <VCardText>
      <VisitForm
      :user_type="props.user_type"
      :student_id="view_student_id"
      @refresh-data="refreshData"
      @close="isDialogFormVisitStudent = false"
      /></VCardText>
      </VCard>
      </VDialog> 
    -->
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
