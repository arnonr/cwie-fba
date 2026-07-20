<script setup>
import VisitView from "@/components/visit/VisitView.vue"
import {
  class_rooms,
  class_years,
  form_statuses,
  statuses,
  statusShow,
  visit_status,
} from "@/data-constant/data"
import axios from "@axios"
import { requiredValidator } from "@validators"
import VueDatePicker from "@vuepic/vue-datepicker"
import "@vuepic/vue-datepicker/dist/main.css"
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"
import { useVisitBookListStore } from "./useVisitBookListStore"

const props = defineProps(["user_type"])

dayjs.extend(buddhistEra)

const visitBookListStore = useVisitBookListStore()

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const isDialogBook = ref(false)
const isDialogViewVisitStudent = ref(false)
const status_check = ref(2)

const selectedItem = ref([])
const refAddBook = ref()
const isAddBookValid = ref(false)

const document = ref({
  document_number: "อว 7125/",
})

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
    // { title: "รอออกหนังสือขอเข้านิเทศ", value: 31 },

    { title: "อยู่ระหว่างประธานนิเทศอนุมัติ", value: 11 },
    { title: "อยู่ระหว่างผู้บริหารอนุมัติ", value: 21 },
    { title: "รอออกหนังสือขอเข้านิเทศ", value: 31 },
    { title: "ออกหนังสือขอเข้านิเทศแล้ว", value: 4 },
    { title: "อยู่ระหว่างตรวจสอบรายงานผล", value: 51 },
    { title: "สำเร็จ", value: 61 },
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
  visitBookListStore
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
  visitBookListStore
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
  visitBookListStore
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

    visitBookListStore
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
  fetchItems()
}

const format = date => {
  const day = dayjs(date).locale("th").format("DD")
  const month = dayjs(date).locale("th").format("MMM")
  const year = date.getFullYear() + 543

  return `${day} ${month} ${year}`
}

// selectItem
const onSelectItemAll = () => {
  selectedItem.value = []
  let check = items.value.filter(d => {
    return d.visit_status > status_check.value
  })
  selectedItem.value = check.map(d => {
    return d.visit_id
  })

  //   chkItem
  console.log(selectedItem.value)
}

const onDisSelectItemAll = () => {
  selectedItem.value = []
  console.log(selectedItem.value)
}

const onAddBook = () => {
  if (selectedItem.value.length != 0) {
    if (advancedSearch.semester_id != "") {
      isDialogBook.value = true
      console.log(document.value.request_document_number)
    }
  } else {
    snackbarText.value = "โปรดเลือกนักศึกษา"
    snackbarColor.value = "error"
    isSnackbarVisible.value = true
    isDialogBook.value = false
  }
}

const onSubmit = () => {
  isOverlay.value = true
  refAddBook.value?.validate().then(({ valid }) => {
    //
    if (valid) {
      visitBookListStore
        .addVisitBook({
          id: selectedItem.value,
          document_number: document.value.document_number,
          document_date:
            document.value.document_date && document.value.document_date != null
              ? dayjs(document.value.document_date).format("YYYY-MM-DD")
              : null,
        })
        .then(response => {
          if (response.status === 200) {
            //
            fetchItems()
            isDialogBook.value = false
            isOverlay.value = false
            snackbarText.value = "ออกหนังสือสำเร็จ"
            snackbarColor.value = "success"
            isSnackbarVisible.value = true
          } else {
            console.log("error")
          }
        })
        .catch(error => {
          console.error(error)
          isOverlay.value = false
        })
    }
    isOverlay.value = false
  })

  //
}
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="หนังสือขอเข้านิเทศ">
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

          <!--
            <VCol
            cols="12"
            sm="4"
            v-if="props.user_type == 'staff' || props.user_type == 'supervisor'"
            >
            <VSelect
            label="สถานะแผนการปฏิบัติงาน"
            v-model="advancedSearch.plan_status"
            density="compact"
            variant="outlined"
            clearable
            :items="selectOptions.plan_statuses"
            />
            </VCol> 
          -->

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
            sm="12"
          >
            <VTextField
              v-model="advancedSearch.company_name"
              label="สถานประกอบการ"
              density="compact"
            />
          </VCol>

          <!-- Action -->
          <VCol
            cols="12"
            class="mb-2 d-flex"
          >
            <VBtn
              color="primary"
              @click="onSelectItemAll"
            >
              เลือกทั้งหมด
            </VBtn>
            <VBtn
              color="error"
              class="ml-2"
              @click="onDisSelectItemAll"
            >
              ยกเลิก
            </VBtn>
            <VSpacer />
            <VBtn
              color="success"
              @click="
                () => {
                  //   document = [];
                  document.document_number = 'อว 7125/';

                  onAddBook();
                }
              "
            >
              ออกหนังสือ
            </VBtn>
          </VCol>
        </VRow>
      </VCardItem>
      <VCardItem class="list-table">
        <VRow>
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
                    เลือก
                  </th>
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
                    อาจารย์นิเทศ
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
                  <td>
                    <VCheckbox
                      v-if="it.visit_id != null && it.visit_status > 2"
                      v-model="selectedItem"
                      class="chkItem"
                      :value="it.visit_id"
                    />
                    <!-- @click="onSelectItem(it.id)" -->
                  </td>
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
                    {{ it.supervision_name }}
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

    <!-- Dialog -->
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

    <!-- Add Form Dialog -->
    <VDialog
      v-model="isDialogBook"
      contained
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        absolute
        @click="isDialogBook = !isDialogBook"
      />

      <!-- Dialog Content -->
      <VCard
        title="แบบฟอร์มออกหนังสือขอเข้านิเทศ"
        class="card-modal"
      >
        <VCardItem class="card-item-modal">
          <VForm
            ref="refAddBook"
            v-model="isAddBookValid"
          >
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="document_number"
                  v-model="document.document_number"
                  label="เลขที่หนังสือ"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol
                cols="12"
                md="12"
                class="align-items-center"
              >
                <label
                  class="v-label font-weight-bold"
                  for="document_date"
                  cols="12"
                  md="4"
                >วันที่หนังสือ :
                </label>
                <VueDatePicker
                  v-model="document.document_date"
                  :enable-time-picker="false"
                  locale="th"
                  auto-apply
                  :format="format"
                >
                  <template #year-overlay-value="{ text }">
                    {{ parseInt(text) + 543 }}
                  </template>
                  <template #year="{ year }">
                    {{ year + 543 }}
                  </template>
                </VueDatePicker>
              </VCol>
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogBook = false"
          >
            Cancel
          </VBtn>
          <VBtn
            id="btn-submit"
            type="submit"
            @click="onSubmit"
          >
            <span>Save</span>
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss" scoped>
.dp__input {
  color: #787878;
}

.list-table > .v-card-item__content {
  overflow: auto !important;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
