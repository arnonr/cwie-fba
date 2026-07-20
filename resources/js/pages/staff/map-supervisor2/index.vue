<script setup>
import { class_rooms, class_years, statuses, form_statuses, statusShow } from "@/data-constant/data"
import { requiredValidator } from "@validators"
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"
import { useCwieDataStore } from "./useCwieDataStore"
import { useStudentStore } from "./useStudentStore"


import VueDatePicker from "@vuepic/vue-datepicker"
import "@vuepic/vue-datepicker/dist/main.css"

import JsonExcel from "vue-json-excel3"
import XLSX from "xlsx"

dayjs.extend(buddhistEra)


//
const readFileAsync = file => {
  return new Promise((resolve, reject) => {
    let reader = new FileReader()

    reader.onload = () => {
      let data = reader.result
      data = new Uint8Array(data)

      let workbook = XLSX.read(data, { type: "array" })
      let first_worksheet = workbook.Sheets[workbook.SheetNames[0]]
      let result = XLSX.utils.sheet_to_json(first_worksheet, { header: 1 })

      // workbook.SheetNames.forEach(function (sheetName) {
      //     var roa = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {header: 1});
      //     if (roa.length) result[sheetName] = roa;
      // });

      resolve(result)
    }

    reader.onerror = reject

    reader.readAsArrayBuffer(file)
  })
}

//
const cwieDataStore = useCwieDataStore()
const studentStore = useStudentStore()

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const items = ref([])
const document = ref({})
const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const selectedItem = ref([])
const importData = ref({ supervisor_file: [], semester_id: "" })

const refAddBook = ref()
const isAddBookValid = ref(false)

// const isSubmit = ref(false);

const isDialogVisible = ref(false)

const advancedSearch = reactive({
  semester_id: "",
  status_id: 11,
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
  book_status: "",
})

const selectOptions = ref({
  perPage: [
    { title: "20", value: 20 },
    { title: "40", value: 40 },
    { title: "60", value: 60 },
    { title: "500", value: 500 },
  ],
  orderBy: [{ title: "ลำดับ/level", value: "level" }],
  order: [
    { title: "น้อย -> มาก", value: "asc" },
    { title: "มาก -> น้อย", value: "desc" },
  ],
  provinces: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  blacklists: [
    { title: "None", value: 0 },
    { title: "Blacklist", value: 1 },
  ],
  semesters: [],
  statuses: statuses,
  class_years: class_years,
  class_rooms: class_rooms,
  teachers: [],
  companies: [],
  book_statuses: [
    { title: "รอออกหนังสือส่งตัว", value: 3 },
    { title: "ออกหนังสือส่งตัวแล้ว", value: 4 },
  ],
})

const fetchProvinces = () => {
  studentStore
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

fetchProvinces()

const fetchSemesters = () => {
  studentStore
    .fetchSemesters()
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
            default_request_doc_no: r.default_request_doc_no,
            default_request_doc_date: r.default_request_doc_date,
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

fetchSemesters()

const fetchTeachers = () => {
  studentStore
    .fetchTeachers({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.teachers = response.data.data.map(r => {
          return {
            title: r.prefix + r.firstname + " " + r.surname,
            value: r.id,
          }
        })

        selectOptions.value.teachers.unshift({
          title: "ไม่มีอาจารย์นิเทศ",
          value: null,
        })

        console.log(selectOptions.value.teachers)

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

const fetchMajors = () => {
  studentStore
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
const fetchItems = () => {
  console.log(advancedSearch.book_status)
  if (advancedSearch.semester_id != "") {
    let search = {
      ...advancedSearch,
      includeAll: true,
      includeForm: true,
    }
    studentStore
      .fetchListStudents({
        perPage: rowPerPage.value,
        currentPage: currentPage.value,
        orderBy: orderBy.value,
        order: order.value,
        ...search,
        includeForm: true,
      })
      .then(response => {
        if (response.status === 200) {
          items.value = response.data.data
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

watchEffect(fetchItems)

watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value
})

watch(
  () => selectedItem.value,
  () => {
    console.log(selectedItem.value)
  },
)

const onAddBook = () => {
  isDialogVisible.value = true
}

const onSubmit = () => {
  isOverlay.value = true

  refAddBook.value?.validate().then(async ({ valid }) => {
    if (valid) {
      let importFile = null
      if (importData.value.supervisor_file.length !== 0) {
        importFile = importData.value.supervisor_file[0]
        let result = await readFileAsync(importFile)
        result.shift()
        console.log(result)
        let data = []
        result.forEach(el => {
          data.push({
            student_code: el[0],
            firstname: el[3],
            surname: el[4],
          })
        })

        //
        cwieDataStore
          .importSupervisor({
            semester_id: importData.value.semester_id,
            data: data,
          })
          .then(response => {
            if (response.status === 200) {
              //
              fetchItems()
              isDialogVisible.value = false
              isOverlay.value = false
              snackbarText.value = "สำเร็จ"
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
    }
    isOverlay.value = false
  })

  //
}

const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")

onMounted(() => {
  window.scrollTo(0, 0)
})

const format = date => {
  const day = dayjs(date).locale("th").format("DD")
  const month = dayjs(date).locale("th").format("MMM")
  const year = date.getFullYear() + 543

  return `${day} ${month} ${year}`
}
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="จับคู่อาจารย์นิเทศ">
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
            sm="2"
          >
            <VTextField
              v-model="advancedSearch.student_code"
              label="รหัสนักศึกษา"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VTextField
              v-model="advancedSearch.firstname"
              label="ชื่อ"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VTextField
              v-model="advancedSearch.surname"
              label="นามสกุล"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="6"
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

          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VSelect
              v-model="advancedSearch.class_year"
              label="ชั้นปี"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_years"
            />
          </VCol>

          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VSelect
              v-model="advancedSearch.class_room"
              label="ห้อง"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_rooms"
            />
          </VCol>

          <VCol
            cols="12"
            sm="6"
          >
            <VSelect
              v-model="advancedSearch.advisor_id"
              label="อาจารย์ที่ปรึกษา"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.teachers"
            />
          </VCol>

          <VCol
            cols="12"
            sm="6"
          >
            <VSelect
              v-model="advancedSearch.supervision_id"
              label="อาจารย์นิเทศ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.teachers"
            />
          </VCol>

          <VCol
            cols="12"
            sm="6"
          >
            <VTextField
              v-model="advancedSearch.company_name"
              label="สถานประกอบการ"
              density="compact"
            />
          </VCol>

          <VCol
            cols="12"
            sm="6"
          >
            <VSelect
              v-model="advancedSearch.book_status"
              label="สถานะการออกหนังสือ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.book_statuses"
            />
          </VCol>

          <!--
            <VCol cols="12" sm="6">
            <VSelect
            label="จังหวัดที่ออกปฏิบัติ"
            v-model="advancedSearch.province_id"
            density="compact"
            variant="outlined"
            clearable
            :items="selectOptions.provinces"
            />
            </VCol> 
          -->

          <!-- Table -->
          <VCol
            cols="12"
            class="mb-2 d-flex"
          >
            <!--
              <VBtn color="primary" @click="onSelectItemAll"> เลือกทั้งหมด</VBtn>
              <VBtn color="error" @click="onDisSelectItemAll" class="ml-2">
              ยกเลิก</VBtn
              > 
            -->
            <VSpacer />
            <VBtn
              color="primary"
              class="mr-2"
              href="http://co-op.fba.kmutnb.ac.th/storage/pdf/template_import_supervisor.xlsx"
              target="_blank"
            >
              ดาวน์โหลด Template
            </VBtn>
            <VBtn
              color="success"
              @click="
                () => {
                  document = [];
                  onAddBook();
                }
              "
            >
              นำเข้าข้อมูล
            </VBtn>
          </VCol>
          <VCol
            cols="12"
            sm="12"
          >
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
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
                    ชื่อ-นามสกุล
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
                  <!-- 👉 User -->
                  <td>
                    <span>
                      {{ it.student_code }}
                    </span>
                  </td>
                  <td class="text-center">
                    {{ it.firstname + " " + it.surname }}
                  </td>
                  <td class="text-center">
                    {{ it.major_name }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ it.company_name }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ responseProvinceName(it.response_province_id) }}
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
                      {{ statusShow(it.status_id) }}
                    </VChip>
                  </td>

                  <!-- 👉 Actions -->
                  <td
                    class="text-center"
                    style="min-width: 80px"
                  >
                    <VBtn
                      color="info"
                      target="_blank"
                      :to="{
                        name: 'staff-students-view-id',
                        params: { id: it.id },
                      }"
                    >
                      View
                    </VBtn>

                    <!--
                      <VBtn
                      color="primary"
                      class="ml-2"
                      :disabled="it.send_document_date == null"
                      @click="
                      () => {
                      selectedItem = [it.form_id];
                      document = [];
                      document.send_document_number =
                      it.send_document_number;
                      document.send_document_date = it.send_document_date;
                      onAddBook();
                      }
                      "
                      >
                      Edit</VBtn
                      > 
                    -->
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

    <!-- Add Form Dialog -->
    <VDialog
      v-model="isDialogVisible"
      contained
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        absolute
        @click="isDialogVisible = !isDialogVisible"
      />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มอัพโหลดไฟล์ จับคู่อาจารย์นิเทศ">
        <VCardItem>
          <VForm
            ref="refAddBook"
            v-model="isAddBookValid"
          >
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow>
              <VCol cols="12">
                <!--
                  <AppTextField
                  id="send_document_number"
                  label="เลขที่หนังสือ"
                  v-model="document.send_document_number"
                  :rules="[requiredValidator]"
                  /> 
                -->
                <label>ปีการศึกษา</label>
                <VSelect
                  v-model="importData.semester_id"
                  density="compact"
                  variant="outlined"
                  clearable
                  :rules="[requiredValidator]"
                  :items="selectOptions.semesters"
                />
              </VCol>

              <VCol cols="12">
                <label> อัพโหลดไฟล์ Excel (.xls, .xlsx) </label>
                <VFileInput
                  id="supervisor_file"
                  v-model="importData.supervisor_file"
                  persistent-placeholder
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogVisible = false"
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

<!-- 1.หน้าแรก -->
<style lang="scss" scoped>
.v-card,
.v-card-item__content {
  overflow: visible !important;
}

.dp__input {
  color: #787878;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
