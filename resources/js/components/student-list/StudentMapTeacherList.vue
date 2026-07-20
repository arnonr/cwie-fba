<script setup>
import StudentView from "@/components/student-view/StudentView.vue"
import { requiredValidator } from "@validators"
import {
  class_rooms,
  class_years,
  statuses,
  form_statuses,
  statusShow,
} from "@/data-constant/data"
import { useStudentListStore } from "./useStudentListStore"
import axios from "@axios"
import JsonExcel from "vue-json-excel3"
import XLSX from "xlsx"
import { useConfigDefaultStore } from "../../store/configDefault"

const props = defineProps(["user_type"])

const configDefaultStore = useConfigDefaultStore()

const studentListStore = useStudentListStore()

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const isDialogViewStudent = ref(false)
const isDialogEditSupervisor = ref(false)
const isDialogImportSupervisor = ref(false)
const refImportSupervision = ref()
const isImportValid = ref(false)

const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")
const importData = ref({ supervisor_file: [], semester_id: "" })
const importResult = ref([])

const checkSemester = ref(true)
const items = ref([])
const provinces = ref([])
const view_student_id = ref(null)
const view_form_id = ref(null)
const major = ref([]) // สำหรับประธานอาจารย์นิเทศ
const supervision_id = ref(null)

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

  if (configDefaultStore.config == undefined) {
    configDefaultStore.fetchConfig().then(() => {
      fetchMajorHeads()
    })
  } else {
    fetchMajorHeads()
  }
} else if (props.user_type == "supervisor") {
  selectOptions.value.approve_statuses = []

  //
} else if (props.user_type == "chairman") {
  selectOptions.value.approve_statuses = []
} else {
  selectOptions.value.approve_statuses = [
    { title: "รอคณะอนุมัติ", value: 5 },
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
  studentListStore
    .fetchSemesters(search)
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.semesters = response.data.data.map(r => {
          if (configDefaultStore.config) {
            advancedSearch.semester_id =
              configDefaultStore.config.staff_year_default
          } else {
            if (r.is_current == 1) {
              advancedSearch.semester_id = r.id
            }
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
  if (configDefaultStore.config == undefined) {
    configDefaultStore.fetchConfig().then(() => {
      fetchSemesters()
    })
  } else {
    fetchSemesters()
  }
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

        selectOptions.value.teachers.unshift({
          title: "ไม่มีอาจารย์นิเทศ",
          value: 999,
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

const onChangeSupervision = () => {
  studentListStore
    .editSupervision({
      id: view_form_id.value,
      supervision_id: supervision_id.value,
    })
    .then(response => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1)
        nextTick(() => {
          isDialogEditSupervisor.value = false
          refreshData()
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

const readFileAsync = file => {
  return new Promise((resolve, reject) => {
    let reader = new FileReader()

    reader.onload = () => {
      let data = reader.result
      data = new Uint8Array(data)

      let workbook = XLSX.read(data, { type: "array" })
      let first_worksheet = workbook.Sheets[workbook.SheetNames[0]]
      let result = XLSX.utils.sheet_to_json(first_worksheet, { header: 1 })
      resolve(result)
    }

    reader.onerror = reject

    reader.readAsArrayBuffer(file)
  })
}

const onImportSubmit = () => {
  isOverlay.value = true

  refImportSupervision.value?.validate().then(async ({ valid }) => {
    if (valid) {
      let importFile = null
      if (importData.value.supervisor_file.length !== 0) {
        importFile = importData.value.supervisor_file[0]
        let result = await readFileAsync(importFile)
        result.shift()

        let data = []
        result.forEach(el => {
          // let s_name_prefix = "";

          if (el.length != 0) {
            let s_firstname = ""
            let s_surname = ""

            if (el[12] != "" && el[12] != null) {
              let arr_prefix = el[12].split(".")

              // for (let i = 0; i < arr_prefix.length - 1; i++) {
              //   s_name_prefix = s_name_prefix + arr_prefix[i] + ".";
              // }

              let fullname = arr_prefix[arr_prefix.length - 1]
              let arr_name = fullname.split(" ")

              s_firstname = arr_name[0]
              for (let i = 0; i < arr_name.length; i++) {
                if (i != 0) {
                  s_surname = s_surname + arr_name[i] + " "
                }
              }

              s_surname = s_surname.trim()
            }

            console.log(el[1])

            data.push({
              level: el[0],
              student_code: el[1].toString().replace("'", ""),
              student_fullname: el[2],
              supervision_fullname: el[12],
              firstname: s_firstname,
              surname: s_surname,
            })
          }
        })

        studentListStore
          .importSupervision({
            semester_id: importData.value.semester_id,
            data: data,
          })
          .then(response => {
            if (response.status === 200) {
              data = data.map(e => {
                let check = response.data.data.find(x => {
                  return x.student_code == e.student_code
                })
                if (check) {
                  e["status"] = check.status
                  e["message"] = check.message
                }

                return e
              })

              console.log(data)

              importResult.value = [...data]

              // response.data.data.map((x) => {
              //   return {
              //     student_code: x.student_code,
              //     // student_name: x.student_name,
              //     // teacher_name:
              //     status: x.status,
              //     message: x.message,
              //   };
              // });
              fetchItems()

              // isDialogVisible.value = false;
              // isOverlay.value = false;
              // snackbarText.value = "สำเร็จ";
              // snackbarColor.value = "success";
              // isSnackbarVisible.value = true;
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
            v-if="props.user_type != 'supervisor'"
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
            cols="12"
            sm="4"
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

          <VCol
            v-if="props.user_type != 'chairman'"
            cols="12"
            sm="4"
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
        </VRow>
      </VCardItem>

      <VCardItem>
        <VCol
          cols="12"
          class="mb-2 d-flex"
        >
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
                // importDocument = [];
                isDialogImportSupervisor = true;
              }
            "
          >
            นำเข้าข้อมูล
          </VBtn>
        </VCol>
      </VCardItem>

      <VCardItem class="list-table">
        <VRow>
          <!-- Table -->
          <VCol
            cols="12"
            md="12"
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
                  <td class="text-center">
                    {{ it.firstname + " " + it.surname }}
                  </td>
                  <td class="text-center">
                    {{ it.major_name }}
                  </td>

                  <td class="text-center">
                    {{ it.company_name }}
                  </td>

                  <td class="text-center">
                    {{ getProvince(it.response_province_id) }}
                  </td>

                  <td class="text-center">
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

                  <!-- 👉 Actions -->
                  <td
                    class="text-center"
                    style="min-width: 80px"
                  >
                    <VBtn
                      color="info"
                      target="_blank"
                      @click="
                        () => {
                          isDialogViewStudent = true;
                          view_student_id = it.id;
                        }
                      "
                    >
                      <!--
                        :to="{
                        name: 'staff-students-view-id',
                        params: { id: it.id },
                        }" 
                      -->
                      View
                    </VBtn>
                  </td>
                  <td
                    class="text-center"
                    style="min-width: 80px"
                  >
                    <VBtn
                      color="info"
                      target="_blank"
                      @click="
                        () => {
                          isDialogEditSupervisor = true;
                          view_student_id = it.id;
                          view_form_id = it.form_id;
                          supervision_id = it.supervision_id;
                        }
                      "
                    >
                      Edit
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
      v-model="isDialogViewStudent"
      persistent
      class="v-dialog-lg"
      style="z-index: 20001"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogViewStudent = !isDialogViewStudent" />

      <!-- Dialog Content -->
      <VCard title="ข้อมูลนักศึกษา">
        <VCardText>
          <StudentView
            :user_type="props.user_type"
            :student_id="view_student_id"
            @refresh-data="refreshData"
            @close="isDialogViewStudent = false"
          />
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isDialogEditSupervisor"
      persistent
      class="v-dialog-lg"
      style="z-index: 2000"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogEditSupervisor = !isDialogEditSupervisor" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มเลือกอาจารย์นิเทศ">
        <VCardText>
          <VCol
            cols="12"
            md="12"
            class="align-items-center"
          >
            <AppAutocomplete
              v-model="supervision_id"
              :items="selectOptions.teachers"
              placeholder="อาจารย์นิเทศ"
              label="อาจารย์นิเทศ"
              density="compact"
              variant="outlined"
              clearable
            />
          </VCol>
        </VCardText>
        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="success"
            @click="onChangeSupervision"
          >
            Submit
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Import Dialog -->
    <VDialog
      v-model="isDialogImportSupervisor"
      contained
      persistent
      class="v-dialog-lg"
      style="z-index: 2000"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        absolute
        @click="isDialogImportSupervisor = !isDialogImportSupervisor"
      />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มอัพโหลดไฟล์ จับคู่อาจารย์นิเทศ">
        <VCardItem>
          <VForm
            ref="refImportSupervision"
            v-model="isImportValid"
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
            @click="isDialogImportSupervisor = false"
          >
            Cancel
          </VBtn>
          <VBtn
            id="btn-submit"
            type="submit"
            @click="onImportSubmit"
          >
            <span>Import</span>
          </VBtn>
        </VCardText>

        <VCardItem class="mb-5">
          <h3>ผลลัพธ์การนำเข้าข้อมูล</h3>
          <VTable>
            <thead class="font-weight-bold">
              <tr>
                <td>รหัสนักศึกษา</td>
                <td>ชื่อนักศึกษา</td>
                <td>อาจารย์นิเทศที่นำเข้า</td>
                <td>ผล</td>
                <td>หมายเหตุ</td>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(it, index) in importResult"
                :key="index"
              >
                <td>{{ it.student_code }}</td>
                <td>{{ it.student_fullname }}</td>
                <td>{{ it.supervision_fullname }}</td>
                <td :class="it.message == 'success' ? 'text-green' : 'text-red'">
                  {{ it.status }}
                </td>
                <td>
                  <span :class="it.message == 'success' ? 'text-green' : 'text-red'">{{ it.message }}</span>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCardItem>
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
