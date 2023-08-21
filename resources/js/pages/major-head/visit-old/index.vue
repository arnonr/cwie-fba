<script setup>
import { class_rooms, class_years, statuses } from "@/data-constant/data";
import { useStudentStore } from "./useStudentStore";

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
const items = ref([]);
const isOverlay = ref(true);
const orderBy = ref("student.id");
const order = ref("desc");
const teacherData = JSON.parse(localStorage.getItem("teacherData"));
const major = ref([]);
const semester = ref([]);

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
  visit_statuses: [
    { title: "รออการอนุมัติจากประธานสาขา", value: 11 },
    { title: "อนุมัติแล้ว", value: 2 },
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

const fetchMajorHeads = () => {
  studentStore
    .fetchMajorHeads({
      teacher_id: teacherData.id,
      perPage: 100,
    })
    .then((response) => {
      if (response.status === 200) {
        semester.value = response.data.data.map((r) => {
          return r.semester_id;
        });

        major.value = response.data.data.map((r) => {
          return r.major_id;
        });

        fetchSemesters(semester.value);
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchMajorHeads();

const fetchSemesters = () => {
  if (semester.value.length != 0) {
    studentStore
      .fetchSemesters({
        id_array: semester.value,
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
  }
};

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
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="ข้อมูลนักศึกษา">
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
              :items="selectOptions.visit_statuses"
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

          <!-- Table -->

          <VCol cols="12" sm="12">
            <div style="overflow-x: auto">
              <VTable class="">
                <!-- 👉 table head -->
                <thead>
                  <tr>
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
                      จัดการ
                    </th>
                  </tr>
                </thead>
                <!-- 👉 table body -->
                <tbody>
                  <tr v-for="it in items" :key="it.id" style="height: 3.75rem">
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

                    <!-- 👉 Actions -->
                    <td class="text-center" style="min-width: 80px">
                      <VBtn
                        v-if="it.visit_id != null"
                        color="success"
                        class="ml-2"
                        :to="{
                          name: 'major-head-visit-view-id',
                          params: { id: it.id },
                        }"
                      >
                        ดูข้อมูล
                      </VBtn>
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
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
