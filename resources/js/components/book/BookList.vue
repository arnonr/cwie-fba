<script setup>
import StudentView from "@/components/student-view/StudentView.vue";
import { requiredValidator } from "@validators";
import {
  class_rooms,
  class_years,
  statuses,
  form_statuses,
  statusShow,
} from "@/data-constant/data";
import { useBookListStore } from "./useBookListStore";
import axios from "@axios";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";

dayjs.extend(buddhistEra);

const bookListStore = useBookListStore();

const props = defineProps(["user_type", "book_type"]);

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const isOverlay = ref(true);
const orderBy = ref("student.id");
const order = ref("desc");
const isDialogViewStudent = ref(false);
const isDialogBook = ref(false);

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const items = ref([]);
const provinces = ref([]);
const view_student_id = ref(null);
const selectedItem = ref([]);
const refAddBook = ref();
const isAddBookValid = ref(false);
const document = ref({
  request_document_number: "อว 7125/",
  send_document_number: "อว 7125/",
});

const status_check = ref(4);

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
  book_status: "",
});

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
  book_statuses: [
    { title: "รอออกหนังสือขอความอนุเคราะห์", value: 1 },
    { title: "ออกหนังสือขอความอนุเคราะห์แล้ว", value: 2 },
  ],
});

selectOptions.value.approve_statuses = [
  { title: "รอคณะอนุมัติ", value: 5 },
  { title: "คณะอนุมัติเรียบร้อย", value: 6 },
];

if (props.book_type == "book-send") {
  selectOptions.value.book_statuses = [
    { title: "รอออกหนังสือส่งตัว", value: 3 },
    { title: "ออกหนังสือส่งตัวแล้ว", value: 4 },
  ];
  selectOptions.value.approve_statuses = [
    { title: "รอตรวจสอบเอกสารตอบรับ", value: 7 },
    { title: "สถานประกอบการตอบรับแล้ว", value: 8 },
  ];

  status_check.value = 6;
}

const fetchSemesters = () => {
  let search = {};
  bookListStore
    .fetchSemesters(search)
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.semesters = response.data.data.map((r) => {
          if (r.is_current == 1) {
            advancedSearch.semester_id = r.id;
          }
          return {
            title: r.term + "/" + r.semester_year + " รอบที่" + r.round_no,
            value: r.id,
            start_date: r.start_date,
            end_date: r.end_date,
          };
        });
        isOverlay.value = false;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchSemesters();

// เฉพาะเมนูของคณะและประธานบริหาร
const fetchTeachers = () => {
  bookListStore
    .fetchTeachers({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.teachers = response.data.data.map((r) => {
          return {
            title: r.prefix + r.firstname + " " + r.surname,
            value: r.id,
          };
        });
        isOverlay.value = false;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchTeachers();

// เฉพาะเมนูของคณะและประธานบริหาร
const fetchMajors = () => {
  bookListStore
    .fetchMajors({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.majors = response.data.data.map((r) => {
          return {
            title: r.name_th,
            value: r.id,
          };
        });
        isOverlay.value = false;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchMajors();

// 👉 Fetching
const fetchProvince = async () => {
  let res = await axios.get("/province", {
    validateStatus: () => true,
  });
  provinces.value = res.data.data;
};
fetchProvince();

const fetchItems = () => {
  let search = {
    ...advancedSearch,
  };

  bookListStore
    .fetchListStudents({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      ...search,
      includeAll: true,
      includeForm: true,
    })
    .then((response) => {
      if (response.status === 200) {
        items.value = response.data.data;
        totalPage.value = response.data.totalPage;
        totalItems.value = response.data.totalData;
        isOverlay.value = false;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

const getProvince = (province_id) => {
  if (province_id == null) return "";
  let res = provinces.value.find((e) => {
    return e.province_id == province_id;
  });
  return res.name_th;
};

watchEffect(fetchItems);

watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value;
});

onMounted(() => {
  window.scrollTo(0, 0);
});

const refreshData = () => {
  fetchItems();
};

const format = (date) => {
  const day = dayjs(date).locale("th").format("DD");
  const month = dayjs(date).locale("th").format("MMM");
  const year = date.getFullYear() + 543;

  return `${day} ${month} ${year}`;
};

// selectItem
const onSelectItemAll = () => {
  selectedItem.value = [];
  let check = items.value.filter((d) => {
    return d.status_id > status_check.value;
  });
  selectedItem.value = check.map((d) => {
    return d.form_id;
  });
  //   chkItem
  console.log(selectedItem.value);
};

const onDisSelectItemAll = () => {
  selectedItem.value = [];
  console.log(selectedItem.value);
};

const onAddBook = () => {
  if (selectedItem.value.length != 0) {
    if (advancedSearch.semester_id != "") {
      isDialogBook.value = true;
      console.log(document.value.request_document_number);
    }
  } else {
    snackbarText.value = "โปรดเลือกนักศึกษา";
    snackbarColor.value = "error";
    isSnackbarVisible.value = true;
    isDialogBook.value = false;
  }
};

const onSubmit = () => {
  isOverlay.value = true;
  refAddBook.value?.validate().then(({ valid }) => {
    //
    if (valid) {
      if (props.book_type == "book_request") {
        bookListStore
          .addRequestBook({
            id: selectedItem.value,
            request_document_number: document.value.request_document_number,
            request_document_date:
              document.value.request_document_date &&
              document.value.request_document_date != null
                ? dayjs(document.value.request_document_date).format(
                    "YYYY-MM-DD"
                  )
                : null,
            max_response_date:
              document.value.max_response_date &&
              document.value.max_response_date != null
                ? dayjs(document.value.max_response_date).format("YYYY-MM-DD")
                : null,
            // send_mail: 1,
          })
          .then((response) => {
            if (response.status === 200) {
              //
              fetchItems();
              isDialogBook.value = false;
              isOverlay.value = false;
              snackbarText.value = "ออกหนังสือสำเร็จ";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;
            } else {
              console.log("error");
            }
          })
          .catch((error) => {
            console.error(error);
            isOverlay.value = false;
          });
      } else {
        bookListStore
          .addSendBook({
            id: selectedItem.value,
            send_document_number: document.value.send_document_number,
            send_document_date:
              document.value.send_document_date &&
              document.value.send_document_date != null
                ? dayjs(document.value.send_document_date).format("YYYY-MM-DD")
                : null,
          })
          .then((response) => {
            if (response.status === 200) {
              //
              fetchItems();
              isDialogBook.value = false;
              isOverlay.value = false;
              snackbarText.value = "ออกหนังสือสำเร็จ";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;
            } else {
              console.log("error");
            }
          })
          .catch((error) => {
            console.error(error);
            isOverlay.value = false;
          });
      }
    }
    isOverlay.value = false;
  });
  //
};
</script>

<template>
  <div>
    <!-- Table -->
    <VCard
      :title="
        props.book_type == 'book_request'
          ? 'หนังสือขอความอนุเคราะห์'
          : 'หนังสือส่งตัว'
      "
    >
      <VCardItem>
        <VRow class="mt-1 mb-1">
          <!-- Search -->
          <VCol cols="12" sm="4">
            <VSelect
              label="จำนวนรายการ/page"
              v-model="rowPerPage"
              density="compact"
              variant="outlined"
              :items="selectOptions.perPage"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="8">
            <VSelect
              label="ปีการศึกษา"
              v-model="advancedSearch.semester_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.semesters"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4" v-if="props.user_type != 'supervisor'">
            <VSelect
              label="สถานะการอนุมัติ"
              v-model="advancedSearch.approve_status"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.approve_statuses"
            />
          </VCol>
          <VSpacer />

          <VCol cols="12" sm="4" v-if="props.user_type == 'staff'">
            <VSelect
              label="สถานะ"
              v-model="advancedSearch.status_id"
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
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.student_code"
              label="รหัสนักศึกษา"
              density="compact"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.firstname"
              label="ชื่อ"
              density="compact"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.surname"
              label="นามสกุล"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            sm="4"
            v-if="props.user_type == 'staff' || props.user_type == 'chairman'"
          >
            <VSelect
              label="สาขาวิชา"
              v-model="advancedSearch.major_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.majors"
            />
          </VCol>

          <VCol cols="12" sm="4">
            <VSelect
              label="ชั้นปี"
              v-model="advancedSearch.class_year"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_years"
            />
          </VCol>

          <VCol cols="12" sm="4" v-if="props.user_type != 'chairman'">
            <VSelect
              label="ห้อง"
              v-model="advancedSearch.class_room"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_rooms"
            />
          </VCol>

          <VCol
            cols="12"
            sm="6"
            v-if="props.user_type == 'staff' || props.user_type == 'chairman'"
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
            v-if="props.user_type == 'staff' || props.user_type == 'chairman'"
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

          <VCol cols="12" sm="6">
            <VTextField
              label="สถานประกอบการ"
              v-model="advancedSearch.company_name"
              density="compact"
            />
          </VCol>

          <VCol cols="12" sm="6">
            <VSelect
              label="สถานะออกหนังสือ"
              v-model="advancedSearch.book_status"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.book_statuses"
            />
          </VCol>

          <!-- Action -->
          <VCol cols="12" class="mb-2 d-flex">
            <VBtn color="primary" @click="onSelectItemAll"> เลือกทั้งหมด</VBtn>
            <VBtn color="error" @click="onDisSelectItemAll" class="ml-2">
              ยกเลิก</VBtn
            >
            <VSpacer />
            <VBtn
              color="success"
              @click="
                () => {
                  //   document = [];
                  document.request_document_number = 'อว 7125/';
                  document.send_document_number = 'อว 7125/';

                  onAddBook();
                }
              "
            >
              ออกหนังสือ</VBtn
            >
          </VCol>

          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable
              class="table"
              style="width: 100%"
              v-if="
                advancedSearch.semester_id != null || props.user_type == 'staff'
              "
            >
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <th scope="col" class="font-weight-bold">เลือก</th>
                  <th scope="col" class="font-weight-bold">รหัสนักศึกษา</th>
                  <th scope="col" class="text-center font-weight-bold">
                    ชื่อ-นามสกุล
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    สาขาวิชา
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    ชั้นปี
                  </th>
                  <th scope="col" class="text-center font-weight-bold">ห้อง</th>
                  <th scope="col" class="text-center font-weight-bold">
                    สถานประกอบการ
                  </th>

                  <th scope="col" class="text-center font-weight-bold">
                    จังหวัด
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    สถานะ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    เลขที่หนังสือ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    จัดการ
                  </th>
                </tr>
              </thead>
              <!-- 👉 table body -->
              <tbody>
                <tr v-for="it in items" :key="it.id" style="height: 3.75rem">
                  <td>
                    <VCheckbox
                      v-model="selectedItem"
                      class="chkItem"
                      :value="it.form_id"
                      v-if="it.status_id > status_check"
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

                  <td class="text-center" style="min-width: 100px">
                    {{ it.class_year }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    {{ it.class_room }}
                  </td>

                  <td class="text-center">
                    {{ it.company_name }}
                  </td>

                  <td class="text-center">
                    {{ getProvince(it.response_province_id) }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <VChip label :color="form_statuses[it.status_id]">{{
                      statusShow(
                        it.status_id,
                        it.request_document_date,
                        it.confirm_response_at
                      )
                    }}</VChip>
                  </td>

                  <td class="text-center">
                    {{
                      props.book_type == "book_request"
                        ? it.request_document_number
                        : it.send_document_number
                    }}
                  </td>

                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
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
                      <!-- :to="{
                        name: 'staff-students-view-id',
                        params: { id: it.id },
                      }" -->
                      View</VBtn
                    >
                  </td>
                </tr>
              </tbody>

              <!-- 👉 table footer  -->
              <tfoot v-show="!items.length">
                <tr>
                  <td colspan="7" class="text-center">No data available</td>
                </tr>
              </tfoot>
              <tfoot v-show="items.length"></tfoot>
            </VTable>
          </VCol>

          <VCol cols="12" sm="12">
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
        <VBtn color="error" @click="isSnackbarVisible = false"> Close </VBtn>
      </template>
    </VSnackbar>

    <VOverlay v-model="isOverlay" contained class="align-center justify-center">
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
        /></VCardText>
      </VCard>
    </VDialog>

    <!-- Add Form Dialog -->
    <VDialog v-model="isDialogBook" contained persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogBook = !isDialogBook" absolute />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มออกหนังสือ" class="card-modal">
        <VCardItem class="card-item-modal">
          <VForm ref="refAddBook" v-model="isAddBookValid">
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow v-if="props.book_type == 'book_request'">
              <VCol cols="12">
                <AppTextField
                  id="request_document_number"
                  label="เลขที่หนังสือ"
                  v-model="document.request_document_number"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="12" class="align-items-center">
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

              <VCol cols="12" md="12" class="align-items-center">
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

            <VRow v-if="props.book_type != 'book_request'">
              <VCol cols="12">
                <AppTextField
                  id="send_document_number"
                  label="เลขที่หนังสือ"
                  v-model="document.send_document_number"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="12" class="align-items-center">
                <label
                  class="v-label font-weight-bold"
                  for="request_document_date"
                  cols="12"
                  md="4"
                  >วันที่หนังสือ :
                </label>
                <VueDatePicker
                  v-model="document.send_document_date"
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
          <VBtn variant="tonal" color="secondary" @click="isDialogBook = false">
            Cancel
          </VBtn>
          <VBtn type="submit" id="btn-submit" @click="onSubmit">
            <span>Save</span></VBtn
          >
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss">
.card-modal,
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
