<script setup>
// ห
import StudentView from "@/components/student-view/StudentView.vue";
import {
  class_rooms,
  class_years,
  statuses,
  form_statuses,
  statusShow,
} from "@/data-constant/data";
import { useStudentListStore } from "./useStudentListStore";
import axios from "@axios";

const studentListStore = useStudentListStore();

const props = defineProps(["user_type"]);

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const isOverlay = ref(true);
const orderBy = ref("student.id");
const order = ref("desc");
const isDialogViewStudent = ref(false);

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const checkSemester = ref(true);
const items = ref([]);
const provinces = ref([]);
const view_student_id = ref(null);
const major = ref([]); // สำหรับประธานอาจารย์นิเทศ
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
});

if (props.user_type == "teacher") {
  const teacherData = JSON.parse(localStorage.getItem("teacherData"));
  advancedSearch.advisor_id = teacherData.id;

  selectOptions.value.approve_statuses = [
    { title: "รอที่ปรึกษาอนุมัติ", value: 1 },
    { title: "ที่ปรึกษาอนุมัติเรียบร้อย", value: 2 },
  ];
} else if (props.user_type == "major-head") {
  const teacherData = JSON.parse(localStorage.getItem("teacherData"));
  selectOptions.value.approve_statuses = [
    { title: "รอประธานอาจารย์นิเทศอนุมัติ", value: 3 },
    { title: "ประธานอาจารย์นิเทศอนุมัติเรียบร้อย", value: 4 },
  ];

  const fetchMajorHeads = () => {
    studentListStore
      .fetchMajorHeads({
        teacher_id: teacherData.id,
        perPage: 100,
      })
      .then((response) => {
        if (response.status === 200) {
          let semester = response.data.data.map((r) => {
            console.log(r.semester_id);
            return r.semester_id;
          });

          major.value = response.data.data.map((r) => {
            return r.major_id;
          });

          if (semester.length == 0) {
            checkSemester.value = false;
          }
          fetchSemesters();
          fetchItems();
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
} else if (props.user_type == "supervisor") {
  selectOptions.value.approve_statuses = [];
  //
} else if (props.user_type == "chairman") {
  selectOptions.value.approve_statuses = [];
} else {
  selectOptions.value.approve_statuses = [
    { title: "รอคณะอนุมัติ", value: 5 },
    { title: "คณะอนุมัติเรียบร้อย", value: 6 },
  ];
}

const fetchSemesters = () => {
  let search = {};

  if (checkSemester.value == false) {
    return;
  }
  if (props.user_type == "chairman") {
    search["chairman_id"] = JSON.parse(localStorage.getItem("teacherData")).id;
  }
  studentListStore
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

if (props.user_type != "major-head") {
  fetchSemesters();
}

// เฉพาะเมนูของคณะและประธานบริหาร
const fetchTeachers = () => {
  studentListStore
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
  studentListStore
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

  if (advancedSearch.semester_id != "") {
    if (props.user_type == "major-head") {
      search["major_id_array"] = major.value;
    }

    if (props.user_type == "supervisor") {
      search["supervision_id"] = JSON.parse(
        localStorage.getItem("teacherData")
      ).id;
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

          <VCol cols="12" sm="12">
            <VTextField
              label="สถานประกอบการ"
              v-model="advancedSearch.company_name"
              density="compact"
            />
          </VCol>
        </VRow>
      </VCardItem>
      <VCardItem class="list-table">
        <VRow>
          <!-- Table -->
          <VCol cols="12" md="12" sm="12">
            <VTable
              class=""
              v-if="
                selectOptions.semesters.length != 0 ||
                props.user_type == 'staff'
              "
            >
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
                    สถานประกอบการ
                  </th>

                  <th scope="col" class="text-center font-weight-bold">
                    จังหวัด
                  </th>
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
