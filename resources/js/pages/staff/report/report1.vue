<script setup>
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"
import JsonExcel from "vue-json-excel3"
import StudentView from "@/components/student-view/StudentView.vue"
import {
  class_rooms,
  class_years,
  statuses,
  form_statuses,
  statusShow,
} from "@/data-constant/data"
import { useReport1Store } from "./useReport1Store"
import axios from "@axios"

const props = defineProps(["user_type"])
const report1Store = useReport1Store()

dayjs.extend(buddhistEra)

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const isDialogViewStudent = ref(false)

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
})

const chooseSemester = ref({})
const json_data = ref([])

const selectOptions = ref({
  perPage: [
    { title: "20", value: 20 },
    { title: "40", value: 40 },
    { title: "60", value: 60 },
    { title: "100", value: 100 },
    { title: "1000", value: 1000 },
  ],
  teachers: [],
  semesters: [],
  companies: [],
  statuses: statuses,
  class_years: class_years,
  class_rooms: class_rooms,
  approve_statuses: [],
  plan_statuses: [
    { title: "รออนุมัติแผน", value: 1 },
    { title: "อนุมัติแผนเรียบร้อย", value: 2 },
  ],
})

if (props.user_type == "teacher") {
  const teacherData = JSON.parse(localStorage.getItem("teacherData"))

  advancedSearch.advisor_id = teacherData.id

  selectOptions.value.approve_statuses = [
    { title: "รอที่ปรึกษาอนุมัติ", value: 1 },
    { title: "ที่ปรึกษาอนุมัติเรียบร้อย", value: 2 },
  ]
} else if (props.user_type == "major-head") {
  const teacherData = JSON.parse(localStorage.getItem("teacherData"))

  selectOptions.value.approve_statuses = [
    { title: "รอประธานอาจารย์นิเทศอนุมัติ", value: 3 },
    { title: "ประธานอาจารย์นิเทศอนุมัติเรียบร้อย", value: 4 },
  ]

  const fetchMajorHeads = () => {
    report1Store
      .fetchMajorHeads({
        teacher_id: teacherData.id,
        perPage: 100,
      })
      .then(response => {
        if (response.status === 200) {
          let semester = response.data.data.map(r => {
            console.log(r.semester_id)
            
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
  selectOptions.value.approve_statuses = []
} else {
  selectOptions.value.approve_statuses = [
    { title: "รอแมพอาจารย์นิเทศ", value: 1 },
    { title: "คณะอนุมัติเรียบร้อย", value: 6 },
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
  report1Store
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
            semester_year: r.semester_year,
            term: r.term,
            round_no: r.round_no,
            data: r,
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
  report1Store
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
  report1Store
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

    report1Store
      .fetchListStudents({
        perPage: rowPerPage.value,
        currentPage: currentPage.value,
        orderBy: orderBy.value,
        order: order.value,
        ...search,
        includeAll: true,
        includeForm: true,
      })
      .then(response => {
        if (response.status === 200) {
          //   items.value = response.data.data;
          // ถ้าสถานะ > 10

          let i = 0
          json_data.value = []
          items.value = response.data.data.map(e => {
            e.level =
              currentPage != 1
                ? i + 1 + (currentPage.value - 1) * rowPerPage.value
                : i + 1
            e.term = chooseSemester.value.term
            e.semester_year = chooseSemester.value.semester_year
            e.response_province_name = getProvince(e.response_province_id)
            e.text_start_date = dayjs(e.start_date)
              .locale("th")
              .format("DD/MM/YYYY")
            console.log(e)
            e.text_end_date = dayjs(e.end_date)
              .locale("th")
              .format("DD/MM/YYYY")
            e.text_status = statusShow(
              e.status_id,
              e.request_document_date,
              e.confirm_response_at,
            )

            json_data.value.push({
              ลำดับ: e.level,
              รหัสนักศึกษา: "'" + e.student_code,
              ชื่อ: e.prefix_name + e.firstname,
              สกุล: e.surname,
              สาขาวิชา: "สาขาวิชา" + e.major_name,
              เทอม: e.term,
              ปีการศึกษา: e.semester_year,
              สถานประกอบการ: e.company_name,
              จังหวัด: e.response_province_name,
              วันเริ่มฝึก: e.text_start_date,
              วันฝึกเสร็จสิ้น: e.text_end_date,
              สถานะ: e.text_status,
              ชื่ออาจารย์นิเทศ: e.supervision_name,

              // ชื่ออาจารย์นิเทศ: s_firstname,
              // คำนำหน้าอาจารย์นิเทศ: s_name_prefix,
              // นามสกุลอาจารย์นิเทศ: s_surname,
              email: e.supervision_email,
            })

            i++
            
            return e
          })
          totalPage.value = response.data.totalPage
          totalItems.value = response.data.totalData
          isOverlay.value = false

          console.log(items.value)
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
watch(advancedSearch, value => {
  console.log(value)
  if (value.semester_id != chooseSemester.value.id) {
    let x = selectOptions.value.semesters.find(
      e => e.value == value.semester_id,
    )
    if (x) {
      chooseSemester.value = x.data
    }
  }
})

onMounted(() => {
  window.scrollTo(0, 0)
})

const refreshData = () => {
  fetchItems()
}
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="ออกรายงานสำหรับจับคู่อาจารย์นิเทศ">
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
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="advancedSearch.approve_status"
              label="สถานะการอนุมัติ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.approve_statuses"
            />
          </VCol>
          <VSpacer />

          <VCol
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
            sm="12"
          >
            <VTextField
              v-model="advancedSearch.company_name"
              label="สถานประกอบการ"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            sm="12"
          >
            <JsonExcel :data="json_data">
              <VBtn color="success">
                Export Excel
              </VBtn>
            </JsonExcel>
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
                  <th
                    scope="col"
                    class="font-weight-bold"
                  >
                    ลำดับ
                  </th>
                  <th
                    scope="col"
                    class="font-weight-bold"
                  >
                    รหัสนักศึกษา
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    ชื่อ
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    นามสกุล
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    สาขาวิชา
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    ปีการศึกษา
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    รอบ
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
                    วันเริ่มฝึก
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    วันที่ฝึกเสร็จสิ้น
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
                    อาจารย์นิเทศ
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    Email
                  </th>
                </tr>
              </thead>
              <!-- 👉 table body -->
              <tbody>
                <tr
                  v-for="(it, index) in items"
                  :key="it.id"
                  style="height: 3.75rem"
                >
                  <td>
                    <span>
                      {{
                        currentPage != 1
                          ? index + 1 + (currentPage - 1) * rowPerPage
                          : index + 1
                      }}
                    </span>
                  </td>
                  <td>
                    <span>
                      {{ it.student_code }}
                    </span>
                  </td>
                  <td class="text-center">
                    {{ it.prefix_name + it.firstname }}
                  </td>
                  <td class="text-center">
                    {{ it.surname }}
                  </td>
                  <td class="text-center">
                    {{ it.major_name }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ it.semester_year }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ it.term }}
                  </td>

                  <td class="text-center">
                    {{ it.company_name }}
                  </td>

                  <td class="text-center">
                    {{ it.response_province_name }}
                  </td>

                  <td class="text-center">
                    {{ it.text_start_date }}
                  </td>

                  <td class="text-center">
                    {{ it.text_end_date }}
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

                  <td class="text-center">
                    {{ it.supervision_name }}
                  </td>
                  <td class="text-center">
                    {{ it.supervision_email }}
                  </td>

                  <!-- 👉 Actions -->
                  <td
                    class="text-center"
                    style="min-width: 80px"
                  />
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
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
