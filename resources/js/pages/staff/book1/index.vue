<script setup>
import { class_rooms, class_years, statuses, form_statuses, statusShow } from "@/data-constant/data"
import { requiredValidator } from "@validators"
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"
import { useCwieDataStore } from "./useCwieDataStore"


import VueDatePicker from "@vuepic/vue-datepicker"
import "@vuepic/vue-datepicker/dist/main.css"

dayjs.extend(buddhistEra)

const cwieDataStore = useCwieDataStore()

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const items = ref([])

const document = ref({
  request_document_number: "อว 7125/",
})

const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const selectedItem = ref([])

const refAddBook = ref()
const isAddBookValid = ref(false)

// const isSubmit = ref(false);

const isDialogVisible = ref(false)

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
    { title: "รอออกหนังสือขอความอนุเคราะห์", value: 3 },
    { title: "ออกหนังสือขอความอนุเคราะห์แล้ว", value: 4 },
  ],
})

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

fetchProvinces()

const fetchSemesters = () => {
  cwieDataStore
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
  cwieDataStore
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

const fetchMajors = () => {
  cwieDataStore
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
  if (advancedSearch.semester_id != "") {
    let search = {
      ...advancedSearch,
      includeAll: true,
    }
    cwieDataStore
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

// selectItem
const onSelectItemAll = () => {
  selectedItem.value = []
  selectedItem.value = items.value.map(d => {
    return d.form_id
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
      isDialogVisible.value = true
      console.log(document.value.request_document_number)
    }
  } else {
    snackbarText.value = "โปรดเลือกนักศึกษา"
    snackbarColor.value = "error"
    isSnackbarVisible.value = true
    isDialogVisible.value = false
  }
}

const onSubmit = () => {
  isOverlay.value = true
  refAddBook.value?.validate().then(({ valid }) => {
    //
    if (valid) {
      cwieDataStore
        .addRequestBook({
          id: selectedItem.value,
          request_document_number: document.value.request_document_number,
          request_document_date:
            document.value.request_document_date &&
            document.value.request_document_date != null
              ? dayjs(document.value.request_document_date).format("YYYY-MM-DD")
              : null,
          max_response_date:
            document.value.max_response_date &&
            document.value.max_response_date != null
              ? dayjs(document.value.max_response_date).format("YYYY-MM-DD")
              : null,
        })
        .then(response => {
          if (response.status === 200) {
            //
            fetchItems()
            isDialogVisible.value = false
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
    <VCard title="หนังสือขอความอนุเคราะห์">
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
                  document.request_document_number = 'อว 7125/';

                  onAddBook();
                }
              "
            >
              ออกหนังสือ
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
                    เลือก
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
                    ชั้นปี
                  </th>
                  <th
                    scope="col"
                    class="text-center font-weight-bold"
                  >
                    ห้อง
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
                    เลขที่หนังสือ
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
                    <VCheckbox
                      v-if="it.status_id > 4"
                      v-model="selectedItem"
                      class="chkItem"
                      :value="it.form_id"
                    />
                    <!-- @click="onSelectItem(it.id)" -->
                  </td>
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
                    {{ it.class_year }}
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ it.class_room }}
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
                        statusShow(it.status_id)
                      }}
                    </VChip>
                  </td>

                  <td
                    class="text-center"
                    style="min-width: 100px"
                  >
                    {{ it.request_document_number }}
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

                    <VBtn
                      color="primary"
                      class="ml-2"
                      :disabled="it.request_document_date == null"
                      @click="
                        () => {
                          selectedItem = [it.form_id];
                          document = {};
                          document.request_document_number =
                            it.request_document_number;
                          document.request_document_date =
                            it.request_document_date;
                          document.max_response_date = it.max_response_date;
                          onAddBook();
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
      <VCard title="แบบฟอร์มออกหนังสือ">
        <VCardItem>
          <VForm
            ref="refAddBook"
            v-model="isAddBookValid"
          >
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="request_document_number"
                  v-model="document.request_document_number"
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
                  for="request_document_date"
                  cols="12"
                  md="4"
                >วันที่หนังสือ :
                </label>
                <VueDatePicker
                  v-model="document.request_document_date"
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

              <VCol
                cols="12"
                md="12"
                class="align-items-center"
              >
                <label
                  class="v-label font-weight-bold"
                  for="request_document_date"
                  cols="12"
                  md="4"
                >วันที่ต้องตอบรับ :
                </label>
                <VueDatePicker
                  v-model="document.max_response_date"
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

<style lang="scss">
.v-card,
.v-card-item__content {
  overflow: visible !important;
}
.dp__input {
  color: #787878;
}
</style>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
