<script setup>
import { class_rooms, class_years, statuses } from "@/data-constant/data";
import { useStudentStore } from "./useStudentStore";

import { form_statuses, text_statuses, statusShow } from "@/data-constant/data";

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

const advancedSearch = reactive({
  semester_id: "",
  status_id: "",
  student_code: "",
  firstname: "",
  surname: "",
  major_id: "",
  class_year: "",
  class_room: "",
  advisor_id: teacherData.id,
  supervision_id: "",
  company_name: "",
  province_id: "",
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
    .fetchSemesters()
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.semesters = response.data.data.map((r) => {
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
  let search = {
    ...advancedSearch,
    includeAll: true,
  };

  //   console.log(localStorage.getItem("userData"));

  studentStore
    .fetchListStudents({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      ...search,
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

watchEffect(fetchItems);

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value;
});

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const resolveActive = (active, type) => {
  let data = "";

  if (active == 1) data = ["success", "Active"];

  if (active == 0) data = ["secondary", "In Active"];

  if (type == "color") {
    return data[0];
  }

  return data[1];
};

const resolveBlacklist = (b, type) => {
  let data = "";

  if (b == 1) data = ["error", "Blacklist"];

  if (b == 0) data = ["secondary", "None"];

  if (type == "color") {
    return data[0];
  }

  return data[1];
};

if (localStorage.getItem("deleted") == 1) {
  snackbarText.value = "Deleted Company";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("deleted");
}

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
              label="สถานะ"
              v-model="advancedSearch.status_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.statuses"
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
          <!-- 
          <VCol cols="12" sm="6">
            <VSelect
              label="อาจารย์ที่ปรึกษา"
              v-model="advancedSearch.advisor_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.teachers"
            />
          </VCol> -->

          <!-- <VCol cols="12" sm="6">
            <VSelect
              label="อาจารย์นิเทศ"
              v-model="advancedSearch.supervision_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.teachers"
            />
          </VCol> -->

          <!-- <VCol cols="12" sm="6">
            <VTextField
              label="สถานประกอบการ"
              v-model="advancedSearch.company_name"
              density="compact"
            />
          </VCol> -->

          <!-- <VCol cols="12" sm="6">
            <VSelect
              label="จังหวัดที่ออกปฏิบัติ"
              v-model="advancedSearch.province_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.provinces"
            />
          </VCol> -->

          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
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
                    สถานะ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    จัดการ
                  </th>
                </tr>
              </thead>
              <!-- 👉 table body -->
              <tbody>
                <tr v-for="it in items" :key="it.id" style="height: 3.75rem">
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

                  <td class="text-center" style="min-width: 100px">
                    {{ it.class_year }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    {{ it.class_room }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <VChip
                      label
                      :color="
                        text_statuses[it.status_id] == 'อาจารย์ที่ปรึกษาอนุมัติ'
                          ? 'success'
                          : form_statuses[it.status_id]
                      "
                      >{{
                        statusShow(
                          it.status_id,
                          it.request_document_date,
                          it.confirm_response_at
                        )
                      }}</VChip
                    >
                  </td>

                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
                    <VBtn
                      color="info"
                      :to="{
                        name: 'teacher-students-view-id',
                        params: { id: it.id },
                      }"
                    >
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
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
