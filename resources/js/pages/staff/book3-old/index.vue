<script setup>
import { class_rooms, class_years, statuses } from "@/data-constant/data";
import { useStudentStore } from "./useStudentStore";
import { requiredValidator } from "@validators";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

import { form_statuses, statusShow, visit_status } from "@/data-constant/data";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
dayjs.extend(buddhistEra);
const studentStore = useStudentStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const document = ref({});
const items = ref([]);
const isOverlay = ref(true);
const orderBy = ref("student.id");
const order = ref("desc");
const major = ref([]);
const selectedItem = ref([]);

const refAddBook = ref();
const isAddBookValid = ref(false);
const isDialogVisible = ref(false);

const advancedSearch = reactive({
  semester_id: "",
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
  visit_status: "",
  book_status: "",
});

const selectOptions = ref({
  perPage: [
    { title: "20", value: 20 },
    { title: "40", value: 40 },
    { title: "60", value: 60 },
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
    { title: "รอออกหนังสือขอเข้านิเทศ", value: 31 },
    { title: "ออกหนังสือขอเข้านิเทศแล้ว", value: 4 },
  ],
});

const fetchProvinces = () => {
  studentStore
    .fetchProvinces({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.provinces = response.data.data.map((r) => {
          return { title: r.name_th, value: r.province_id };
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
fetchProvinces();

const fetchSemesters = () => {
  studentStore
    .fetchSemesters({
      perPage: 100,
    })
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

const fetchTeachers = () => {
  studentStore
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

const fetchMajors = () => {
  studentStore
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
const fetchItems = () => {
  if (advancedSearch.semester_id != "") {
    let search = {
      ...advancedSearch,
      includeAll: true,
      major_id_array: major.value,
    };
    studentStore
      .fetchListStudents({
        perPage: rowPerPage.value,
        currentPage: currentPage.value,
        orderBy: orderBy.value,
        order: order.value,
        ...search,
        includeForm: true,
        includeVisit: true,
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
  }
};

watchEffect(fetchItems);

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value;
});

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const responseProvinceName = (response_province_id) => {
  if (response_province_id) {
    let response_province_select = selectOptions.value.provinces.find((x) => {
      return x.value == response_province_id;
    });
    return response_province_select.title;
  } else {
    return "-";
  }
};

onMounted(() => {
  window.scrollTo(0, 0);
});

// selectItem
const onSelectItemAll = () => {
  selectedItem.value = [];
  selectedItem.value = items.value.map((d) => {
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
      isDialogVisible.value = true;
    }
  } else {
    snackbarText.value = "โปรดเลือกนักศึกษา";
    snackbarColor.value = "error";
    isSnackbarVisible.value = true;
    isDialogVisible.value = false;
  }
};

const onSubmit = () => {
  isOverlay.value = true;
  refAddBook.value?.validate().then(({ valid }) => {
    //
    if (valid) {
      studentStore
        .addVisitBook({
          visit_id: selectedItem.value,
          document_number: document.value.document_number,
          document_date:
            document.value.document_date && document.value.document_date != null
              ? dayjs(document.value.document_date).format("YYYY-MM-DD")
              : null,
        })
        .then((response) => {
          if (response.status === 200) {
            //
            fetchItems();
            isDialogVisible.value = false;
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
    isOverlay.value = false;
  });
  //
};

const format = (date) => {
  const day = dayjs(date).locale("th").format("DD");
  const month = dayjs(date).locale("th").format("MMM");
  const year = date.getFullYear() + 543;

  return `${day} ${month} ${year}`;
};
</script>

<style lang="scss">
.v-card,
.v-card-item__content {
  overflow: visible !important;
}

.dp__input {
  color: #787878;
}
</style>

<template>
  <div>
    <!-- Table -->
    <VCard title="หนังสือขอออกนิเทศ">
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
          <VCol cols="12" sm="4">
            <VSelect
              label="สถานะการขอออกนิเทศ"
              v-model="advancedSearch.visit_status"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.book_statuses"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="2">
            <VTextField
              v-model="advancedSearch.student_code"
              label="รหัสนักศึกษา"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="3">
            <VTextField
              v-model="advancedSearch.firstname"
              label="ชื่อ"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="3">
            <VTextField
              v-model="advancedSearch.surname"
              label="นามสกุล"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="6">
            <VSelect
              label="สาขาวิชา"
              v-model="advancedSearch.major_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.majors"
            />
          </VCol>

          <VSpacer />
          <VCol cols="12" sm="3">
            <VSelect
              label="ชั้นปี"
              v-model="advancedSearch.class_year"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_years"
            />
          </VCol>

          <VSpacer />
          <VCol cols="12" sm="3">
            <VSelect
              label="ห้อง"
              v-model="advancedSearch.class_room"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_rooms"
            />
          </VCol>

          <VCol cols="12" sm="6">
            <VSelect
              label="อาจารย์นิเทศ"
              v-model="advancedSearch.supervision_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.teachers"
            />
          </VCol>

          <VCol cols="12" sm="6">
            <VTextField
              label="สถานประกอบการ"
              v-model="advancedSearch.company_name"
              density="compact"
            />
          </VCol>

          <!-- Table -->

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
                  document.document_number = 'อว 7125/';
                  onAddBook();
                }
              "
            >
              ออกหนังสือ</VBtn
            >
          </VCol>

          <VCol cols="12" sm="12">
            <div style="overflow-x: auto">
              <VTable class="">
                <!-- 👉 table head -->
                <thead>
                  <tr>
                    <th scope="col" class="font-weight-bold">เลือก</th>
                    <th scope="col" class="text-center font-weight-bold">
                      อาจารย์นิเทศ
                    </th>
                    <th scope="col" class="text-center font-weight-bold">
                      ชื่อ-นามสกุล
                    </th>
                    <th scope="col" class="text-center font-weight-bold">
                      สถานประกอบการ
                    </th>
                    <th scope="col" class="text-center font-weight-bold">
                      จังหวัด
                    </th>
                    <!-- <th scope="col" class="text-center font-weight-bold">
                      สถานะ
                    </th> -->
                    <th scope="col" class="text-center font-weight-bold">
                      สถานะใบขอออกนิเทศ
                    </th>
                    <th scope="col" class="text-center font-weight-bold">
                      วันที่ออกนิเทศ
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
                        :value="it.visit_id"
                        v-if="it.visit_status > 2"
                      />
                      <!-- @click="onSelectItem(it.id)" -->
                    </td>
                    <td class="text-center">
                      {{ it.supervision_name }}
                    </td>
                    <td class="text-center">
                      {{ it.firstname + " " + it.surname }}
                    </td>
                    <td class="text-center" style="min-width: 100px">
                      {{ it.company_name }}
                    </td>

                    <td class="text-center" style="min-width: 100px">
                      {{ responseProvinceName(it.response_province_id) }}
                    </td>

                    <td class="text-center">
                      <span v-if="it.visit_status">
                        <VChip
                          label
                          :color="visit_status[it.visit_status].color"
                          >{{ visit_status[it.visit_status].title }}</VChip
                        >
                      </span>
                    </td>
                    <td class="text-center" style="min-width: 100px">
                      {{
                        it.visit_date
                          ? dayjs(it.visit_date)
                              .locale("th")
                              .format("DD MMM BB") +
                            " " +
                            it.visit_time
                          : ""
                      }}
                    </td>

                    <td class="text-center" style="min-width: 100px">
                      {{ it.visit_document_number }}
                    </td>

                    <!-- 👉 Actions -->
                    <td class="text-center" style="min-width: 80px">
                      <VBtn
                        v-if="it.visit_id != null"
                        color="info"
                        class="ml-2"
                        target="_blank"
                        :to="{
                          name: 'staff-book3-view-id',
                          params: { id: it.id },
                        }"
                      >
                        View
                      </VBtn>
                      <!-- <VBtn
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
                      > -->
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
            </div>
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

    <!-- Add Form Dialog -->
    <VDialog v-model="isDialogVisible" contained persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" absolute />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มออกหนังสือ">
        <VCardItem>
          <VForm ref="refAddBook" v-model="isAddBookValid">
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="send_document_number"
                  label="เลขที่หนังสือ"
                  v-model="document.document_number"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="12" class="align-items-center">
                <label
                  class="v-label font-weight-bold"
                  for="send_document_date"
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
            @click="isDialogVisible = false"
          >
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

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
