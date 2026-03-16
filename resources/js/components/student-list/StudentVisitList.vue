<script setup>
import VisitForm from "@/components/visit/VisitForm.vue";
import VisitView from "@/components/visit/VisitView.vue";
import Vue3Html2pdf from "vue3-html2pdf";

// import "@/font/Sarabun-normal";
import {
  class_rooms,
  class_years,
  form_statuses,
  statuses,
  statusShow,
  visit_status,
} from "@/data-constant/data";
import axios from "@axios";
import { bahttext } from "bahttext";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import Swal from "sweetalert2";
import { useStudentListStore } from "./useStudentListStore";

const props = defineProps(["user_type"]);

dayjs.extend(buddhistEra);

const studentListStore = useStudentListStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const isOverlay = ref(true);
const orderBy = ref("student.id");
const order = ref("desc");
const isDialogFormVisitStudent = ref(false);
const isDialogViewVisitStudent = ref(false);

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const checkSemester = ref(true);
const items = ref([]);
const provinces = ref([]);
const amphurs = ref([]);
const tumbols = ref([]);
const supervisor_id = ref(null);
const supervisor = ref({});
const view_student_id = ref(null);
const major = ref([]); // สำหรับประธานอาจารย์นิเทศ
let travel_array = ref([]);

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
});

const teacherData = ref({});
const total_travel = ref(0);
const visit_expense_all = ref(0);

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
  visit_statuses: [
    { title: "รอขอออกนิเทศ", value: 0 },
    { title: "ขอออกนิเทศแล้ว", value: 1 },
  ],
});

// PDF
const countPDF = ref(0);
const studentListPDF = ref([]);
const chairmanPDF = ref({ data: {} });
let isAdminSecret = JSON.parse(localStorage.getItem("isAdminSecret"));
if (props.user_type == "teacher") {
  teacherData.value = JSON.parse(localStorage.getItem("teacherData"));
  advancedSearch.advisor_id = teacherData.value.id;

  selectOptions.value.approve_statuses = [
    { title: "รอที่ปรึกษาอนุมัติ", value: 1 },
    { title: "ที่ปรึกษาอนุมัติเรียบร้อย", value: 2 },
  ];
} else if (props.user_type == "major-head") {
  teacherData.value = JSON.parse(localStorage.getItem("teacherData"));
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
  teacherData.value = JSON.parse(localStorage.getItem("teacherData"));
  supervisor_id.value = teacherData.value.id;
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

//
const fetchProvince = async () => {
  let res = await axios.get("/province", {
    validateStatus: () => true,
  });
  provinces.value = res.data.data;
};

const fetchAmphur = async () => {
  let res = await axios.get("/amphur", {
    validateStatus: () => true,
  });
  amphurs.value = res.data.data;
};

const fetchTumbol = async () => {
  let res = await axios.get(
    "/tumbol",
    { perPage: 20000 },
    {
      validateStatus: () => true,
    }
  );
  tumbols.value = res.data.data;
};

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
            term: r.term,
            semester_year: r.semester_year,
            semester_visit_expense: r.semester_visit_expense,
            chairman_id: r.chairman_id,
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
const fetchTeachers = async () => {
  await studentListStore
    .fetchTeachers({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.teachers = response.data.data.map((r) => {
          return {
            title: r.prefix + r.firstname + " " + r.surname,
            value: r.id,
            data: r,
          };
        });

        supervisor.value = response.data.data.find((r) => {
          return r.id == supervisor_id.value;
        });

        chairmanPDF.value = selectOptions.value.teachers.find((e) => {
          return e.value == semesterPDF.value.chairman_id;
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

// เฉพาะเมนูของคณะและประธานบริหาร
const fetchMajors = async () => {
  await studentListStore
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

// 👉 Fetching

const fetchItems = () => {
  let search = {
    ...advancedSearch,
  };

  if (advancedSearch.semester_id != "" && advancedSearch.semester_id != null) {
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
        includeVisit: true,
      })
      .then((response) => {
        if (response.status === 200) {
          let itemsSort = response.data.data;

          items.value = itemsSort;
          totalPage.value = response.data.totalPage;
          totalItems.value = response.data.totalData;

          //   items
          //   items.value.sort(function (a, b) {
          //     return new Date(b.visit_date) - new Date(a.visit_date);
          //   });

          isOverlay.value = false;
        } else {
          console.log("error");
        }
      })
      .catch((error) => {
        console.error(error);
        isOverlay.value = false;
      });
  } else {
    items.value = [];
  }
};

const getProvince = (province_id) => {
  if (province_id == null) return "";
  let res = provinces.value.find((e) => {
    return e.province_id == province_id;
  });

  return res.name_th;
};

const getAmphur = (id) => {
  if (id == null || amphurs.value.length == 0) return "";
  let res = amphurs.value.find((e) => {
    return e.amphur_id == id;
  });

  return res.name_th;
};

const getTumbol = (id) => {
  if (id == null || tumbols.value.length == 0) return "";
  let res = tumbols.value.find((e) => {
    return e.tumbol_id == id;
  });

  return res.name_th;
};

watchEffect(fetchItems);

watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value;
});

const semesterPDF = ref({});

watch(advancedSearch, (value) => {
  if (advancedSearch.semester_id != null) {
    semesterPDF.value = selectOptions.value.semesters.find((e) => {
      return e.value == advancedSearch.semester_id;
    });
  }
});

onMounted(async () => {
  await fetchProvince();
  await fetchAmphur();
  await fetchTumbol();
  await fetchTeachers();

  await fetchMajors();

  window.scrollTo(0, 0);
});

const refreshData = () => {
  isDialogFormVisitStudent.value = false;
  isDialogViewVisitStudent.value = false;
  fetchItems();
};

const html2Pdf = ref(null);

const generatePayment = () => {
  html2Pdf.value.generatePdf();
};

const onPayment = () => {
  let search = {
    ...advancedSearch,
  };

  if (props.user_type == "supervisor") {
    search["supervision_id"] = JSON.parse(
      localStorage.getItem("teacherData")
    ).id;
  }

  studentListStore
    .fetchListStudents({
      perPage: 10000,
      currentPage: 1,
      orderBy: orderBy.value,
      order: order.value,
      ...search,
      includeAll: true,
      includeForm: true,
      includeVisit: true,
    })
    .then((response) => {
      if (response.status === 200) {
        let check = response.data.data.find((e) => {
          return e.visit_status < 6;
        });

        if (check) {
          Swal.fire({
            icon: "error",
            title: "ยังส่งรายงานการออกนิเทศไม่ครบ!",
            text: "โปรดตรวจสอบการส่งรายงานการออกนิเทศ",
            customClass: {
              confirmButton:
                "v-btn v-btn--elevated v-theme--light bg-success v-btn--density-default v-btn--size-default v-btn--variant-elevated",
            },
          });
        } else {
          // Generate PDF
          countPDF.value = response.data.data.length;

          let students = response.data.data.sort(
            (a, b) => new Date(a.visit_date) - new Date(b.visit_date)
          );

          let ta = [];

          // ตั้งค่า ta = [] เก็บ วันที่ที่มี caompany ด้านใน
          // วนรอบรายการ ตรวจสอบว่าวันที่ซ้ำหรือยัง ถ้าใหม่ให้ push Data (ชื่อบริษัท จังหวัด ราคา โชว์ราคาหรือไม่ โชว์ไปก่อน)
          // ถ้าวันที่ซ้ำ ให้ตรวจสอบว่าบริษัทเดียวกันและจังหวัดเดียวกันหรือไม่ ถ้าใช่ซ้ำไม่ต้อง push
          // ถ้าไม่ซ้ำให้ push Data (ชื่อบริษัท จังหวัด ราคา โชว์ราคาหรือไม่ โชว์ไปก่อน) ตรวจสอบว่า อันไหนแพงกว่าให้ false อันอื่นๆไปให้หมด

          students.forEach((e) => {
            let findDate = ta.find((x) => x.date == e.visit_date);

            if (findDate) {
              // ถ้ามีวันที่อยู่แล้วทำเงื่อนไขนี้
              let checkCompany = findDate.companies.find((x) => {
                return (
                  x.id == e.company_id && x.province_id == e.visit_province_id
                );
              });

              if (!checkCompany) {
                findDate.companies.push({
                  id: e.company_id,
                  name: e.company_name,
                  province_id: e.visit_province_id,
                  price:
                    e.visit_travel_expense != null ? e.visit_travel_expense : 0,
                });

                findDate.companies2.push({
                  id: e.company_id,
                  name: e.company_name,
                  province_id: e.visit_province_id,
                  price:
                    e.visit_travel_expense != null ? e.visit_travel_expense : 0,
                  show_price: false,
                });
              } else {
                findDate.companies2.push({
                  id: e.company_id,
                  name: e.company_name,
                  province_id: e.visit_province_id,
                  price:
                    e.visit_travel_expense != null ? e.visit_travel_expense : 0,
                  show_price: false,
                });
              }

              if (findDate.price.expense < e.visit_travel_expense) {
                findDate.price = {
                  id: e.company_id,
                  expense: e.visit_travel_expense,
                  expense_num:
                    e.visit_travel_expense != null ? e.visit_travel_expense : 0,
                };
              }
            } else {
              // ถ้าไม่มีวันที่อยู่
              ta.push({
                date: e.visit_date,
                companies: [
                  {
                    id: e.company_id,
                    name: e.company_name,
                    province_id: e.visit_province_id,
                    price:
                      e.visit_travel_expense != null
                        ? e.visit_travel_expense
                        : 0,
                    show_price: true,
                  },
                ],
                companies2: [
                  {
                    id: e.company_id,
                    name: e.company_name,
                    province_id: e.visit_province_id,
                    price:
                      e.visit_travel_expense != null
                        ? e.visit_travel_expense
                        : 0,
                    show_price: true,
                  },
                ],
                price: {
                  id: e.company_id,
                  expense:
                    e.visit_travel_expense != null
                      ? e.visit_travel_expense.toLocaleString()
                      : null,
                  expense_num:
                    e.visit_travel_expense != null ? e.visit_travel_expense : 0,
                },
              });
            }
          });

          travel_array.value = ta;

          travel_array.value.forEach((el) => {
            total_travel.value =
              Number(total_travel.value) + Number(el.price.expense_num);
          });
          studentListPDF.value = students;

          visit_expense_all.value = (
            semesterPDF.value.semester_visit_expense * countPDF.value
          ).toLocaleString();
          generatePayment();
        }
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });

  // if()
  // check ว่าครบทุกคนไหม ถ้าไม่ครบ alert
  // ถ้าครบแล้วกดออกใบได้ที่ function PDF
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
              v-model="rowPerPage"
              label="จำนวนรายการ/page"
              density="compact"
              variant="outlined"
              :items="selectOptions.perPage"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="8">
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
          <VCol v-if="props.user_type != 'supervisor'" cols="12" sm="4">
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

          <VCol v-if="props.user_type == 'staff'" cols="12" sm="4">
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

          <VCol cols="12" sm="4">
            <VSelect
              v-model="advancedSearch.visit_status"
              label="สถานะการขอออกนิเทศ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.visit_statuses"
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

          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.company_name"
              label="สถานประกอบการ"
              density="compact"
            />
          </VCol>

          <!-- Table -->
          <VCol cols="12" sm="12">
            <VBtn
              color="primary"
              class="ml-2"
              :disabled="
                advancedSearch.semester_id == null ||
                advancedSearch.semester_id == ''
              "
              @click="
                () => {
                  onPayment();
                }
              "
            >
              พิมพ์เอกสารเบิกจ่าย
            </VBtn>
          </VCol>

          <VCol cols="12" sm="12">
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
                  <!-- <th scope="col" class="font-weight-bold">รหัสนักศึกษา</th> -->
                  <th scope="col" class="text-center font-weight-bold">
                    ชื่อ-นามสกุล
                  </th>
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
                    {{ it.firstname + " " + it.surname }}
                  </td>
                  <td class="text-center">
                    {{ it.company_name }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    {{ getProvince(it.workplace_province_id) }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <VChip label :color="form_statuses[it.status_id]">
                      {{
                        statusShow(
                          it.status_id,
                          it.request_document_date,
                          it.confirm_response_at
                        )
                      }}
                    </VChip>
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <span v-if="it.visit_status">
                      <VChip
                        v-if="it.visit_reject_status_id == null"
                        label
                        :color="visit_status[it.visit_status].color"
                        >{{ visit_status[it.visit_status].title }}</VChip
                      >
                      <VChip v-else label color="error">รอแก้ไขข้อมูล</VChip>

                      <VChip
                        v-if="
                          it.visit_status == 4 && it.visit_report_status_id == 3
                        "
                        label
                        color="error"
                        class="mt-2"
                        >รอแก้ไขรายงาน</VChip
                      >
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

                    <!-- :disabled="it.status_id < 13 ? true : false" -->
                    <VBtn
                      v-if="it.visit_status < 5"
                      color="warning"
                      class="ml-2 mt-2"
                      :disabled="
                        it.status_id < 13 && isAdminSecret != 1 ? true : false
                      "
                      @click="
                        () => {
                          view_student_id = it.id;
                          isDialogFormVisitStudent = true;
                        }
                      "
                    >
                      {{
                        it.visit_id != null ? "แก้ไขใบขอออกนิเทศ" : "ออกนิเทศ"
                      }}
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
              <tfoot v-show="items.length" />
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
      v-model="isDialogViewVisitStudent"
      persistent
      class="v-dialog-lg"
      style="z-index: 2000"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogViewVisitStudent = !isDialogViewVisitStudent"
      />

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

    <VDialog
      v-model="isDialogFormVisitStudent"
      persistent
      class="v-dialog-lg"
      style="z-index: 2000"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogFormVisitStudent = !isDialogFormVisitStudent"
      />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มขอออกนิเทศ">
        <VCardText>
          <VisitForm
            :user_type="props.user_type"
            :student_id="view_student_id"
            @refresh-data="refreshData"
            @close="isDialogFormVisitStudent = false"
          />
        </VCardText>
      </VCard>
    </VDialog>

    <!--  -->
    <Vue3Html2pdf
      ref="html2Pdf"
      :show-layout="false"
      :float-layout="true"
      :enable-download="true"
      :preview-modal="true"
      :paginate-elements-by-height="1400"
      filename="payment"
      :pdf-quality="2"
      :manual-pagination="false"
      pdf-format="a4"
      pdf-orientation="portrait"
      pdf-content-width="800px"
    >
      <!--
        @progress="onProgress($event)"
        @hasStartedGeneration="hasStartedGeneration()"
        @hasGenerated="hasGenerated($event)"
      -->

      <template #pdf-content>
        <!-- PDF Content Here -->
        <div
          style="margin-right: 50px; margin-bottom: 50px; margin-left: 50px"
          class="payment-pdf"
        >
          <div class="text-right" style="margin-top: 50px">
            ที่คณะบริหารธุรกิจมหาวิทยาลัยเทคโนโลยีพระจอมเกล้าพระนครเหนือ
          </div>
          <div class="text-center font-weight-bold" style="margin-top: 50px">
            ใบสำคัญรับเงิน
          </div>

          <div class="" style="margin-top: 20px; margin-left: 400px">
            วันที่
            ................................................................................
          </div>

          <div class="" style="margin-top: 20px; margin-left: 120px">
            ข้าพเจ้า
            ...................................................................
            อยู่บ้านเลขที่........................................................................
          </div>
          <div class="" style="margin-top: 20px">
            แขวง/ตำบล .....................................................
            เขต/อำเภอ......................................................
            จังหวัด....................................................
          </div>

          <div class="" style="margin-top: 20px">
            ได้รับเงินจากมหาวิทยาลัยเทคโนโลยีพระจอมเกล้าพระนครเหนือดังรายการต่อไปนี้
          </div>
          <div class="" style="margin-top: 20px">
            <table class="table-payment">
              <tr>
                <th class="text-center" style="width: 500px">รายการ</th>
                <th class="text-center" style="width: 200px">จำนวนเงิน</th>
              </tr>
              <tr>
                <td
                  class=""
                  style="
                    height: 300px;
                    padding-top: 10px;
                    padding-bottom: 10px;
                    padding-left: 10px;
                    vertical-align: top;
                  "
                >
                  <span>
                    ค่าตอบแทนการนิเทศนักศึกษาปฏิบัติงานสหกิจศึกษา<br />
                    <!-- โครงการนิเทศนักศึกษาปฏิบัติงานสหกิจศึกษาคณะบริหารธุรกิจ<br /> -->
                    ภาคการศึกษาที่ &nbsp; &nbsp; ปีการศึกษา <br />
                    (จำนวน.............คน คนละ.......................บาท)
                  </span>
                </td>
                <td
                  class="text-right"
                  style="
                    height: 300px;
                    padding-top: 10px;
                    padding-right: 10px;
                    padding-bottom: 10px;
                    padding-left: 10px;
                    vertical-align: top;
                  "
                />
              </tr>
              <tr>
                <td
                  class="text-right"
                  style="
                    padding-top: 10px;
                    padding-right: 10px;
                    padding-bottom: 10px;
                    padding-left: 10px;
                    vertical-align: top;
                  "
                >
                  <span> รวมเป็นเงิน (บาท) </span>
                </td>
                <td
                  class="text-right"
                  style="
                    padding-top: 10px;
                    padding-right: 10px;
                    padding-bottom: 10px;
                    padding-left: 10px;
                    vertical-align: top;
                  "
                />
              </tr>
            </table>
          </div>

          <div style="margin-top: 20px">
            <span>จำนวนเงิน (ตัวอักษร)</span>
            <span
              >..................................................................</span
            >
          </div>

          <div style="margin-top: 80px; margin-left: 300px">
            <span
              >...............................................................................</span
            >
            <span>ผู้รับเงิน</span><br />
            <span style="padding-left: 40px" />
          </div>

          <div style="margin-top: 80px; margin-left: 300px">
            <span
              >...............................................................................</span
            >
            <span>ผู้จ่ายเงิน</span><br />
            <span style="padding-left: 40px" />
          </div>
        </div>

        <!-- Absolute Page1 -->
        <!--
          <span style="position: absolute; top: 162px; left: 500px"
          >{{ dayjs().locale("th").format("DD MMMM BBBB") }}
          </span>
        -->
        <span
          style="position: absolute; top: 199px; left: 220px; font-size: 13px"
          >{{
            supervisor.prefix + supervisor.firstname + " " + supervisor.surname
          }}</span
        >
        <span
          style="position: absolute; top: 199px; left: 510px; font-size: 13px"
          >{{ supervisor.address }}
        </span>
        <span
          style="position: absolute; top: 238px; left: 130px; font-size: 13px"
          >{{ getTumbol(supervisor.tumbol_id) }}
        </span>

        <span
          style="position: absolute; top: 238px; left: 360px; font-size: 13px"
          >{{ getAmphur(supervisor.amphur_id) }}
        </span>

        <span
          style="position: absolute; top: 238px; left: 580px; font-size: 13px"
          >{{ getProvince(supervisor.province_id) }}
        </span>

        <span
          style="position: absolute; top: 368px; left: 147px; font-size: 13px"
          >{{ semesterPDF.term }}
        </span>

        <span
          style="position: absolute; top: 368px; left: 228px; font-size: 13px"
          >{{ semesterPDF.semester_year }}
        </span>

        <span
          style="position: absolute; top: 389px; left: 120px; font-size: 13px"
          >{{ countPDF }}
        </span>

        <span
          style="position: absolute; top: 389px; left: 210px; font-size: 13px"
          >{{ semesterPDF.semester_visit_expense }}.00
        </span>

        <span
          style="position: absolute; top: 350px; left: 680px; font-size: 13px"
          >{{ visit_expense_all }}.00
        </span>

        <span
          style="position: absolute; top: 650px; left: 680px; font-size: 13px"
          >{{ visit_expense_all }}.00
        </span>

        <span
          style="position: absolute; top: 699px; left: 200px; font-size: 13px"
          >{{ bahttext(countPDF * semesterPDF.semester_visit_expense) }}
        </span>

        <!-- bahttext() -->

        <span
          style="position: absolute; top: 828px; left: 400px; font-size: 13px"
          >{{
            "( " +
            supervisor.prefix +
            supervisor.firstname +
            " " +
            supervisor.surname +
            " )"
          }}</span
        >

        <span
          style="position: absolute; top: 945px; left: 400px; font-size: 13px"
          >{{
            "( " +
            chairmanPDF.data.prefix +
            chairmanPDF.data.firstname +
            " " +
            chairmanPDF.data.surname +
            " )"
          }}</span
        >

        <div class="html2pdf__page-break" />
        <!-- Page2 -->
        <div
          style="margin-right: 50px; margin-bottom: 50px; margin-left: 50px"
          class="payment-pdf"
        >
          <div class="pdf-page">
            <div class="text-center font-weight-bold" style="margin-top: 49px">
              ตารางสรุปค่าตอบแทนการนิเทศและค่าเดินทางในการนิเทศนักศึกษาสหกิจศึกษา<br />
              ของอาจารย์นิเทศคณะบริหารธุรกิจ
            </div>

            <div class="" style="margin-top: 20px">
              <span />
            </div>

            <div class="" style="margin-top: 60px; font-size: 10px">
              <table class="table-payment">
                <tr>
                  <th class="text-center" style="width: 150px">ว-ด-ป</th>
                  <th class="text-center" style="width: 150px">รหัสนักศึกษา</th>
                  <th class="text-center" style="width: 300px">ชื่อ-สกุล</th>
                  <th class="text-center" style="width: 300px">บริษัท</th>
                  <th class="text-center" style="width: 150px">จังหวัด</th>
                </tr>
                <tr v-for="(it, index) in studentListPDF" :key="index">
                  <td
                    class="text-center"
                    style="
                      padding-top: 10px;
                      padding-bottom: 10px;
                      padding-left: 10px;
                    "
                  >
                    <span>
                      {{
                        dayjs(it.visit_date).locale("th").format("DD MMM BB")
                      }}
                    </span>
                  </td>
                  <td
                    style="
                      padding-top: 10px;
                      padding-right: 10px;
                      padding-bottom: 10px;
                      padding-left: 10px;
                    "
                    class="text-center"
                  >
                    {{ it.student_code }}
                  </td>
                  <td
                    style="
                      padding-top: 10px;
                      padding-right: 10px;
                      padding-bottom: 10px;
                      padding-left: 10px;
                    "
                  >
                    {{ it.prefix_name + it.firstname + " " + it.surname }}
                  </td>
                  <td
                    style="
                      padding-top: 10px;
                      padding-right: 10px;
                      padding-bottom: 10px;
                      padding-left: 10px;
                    "
                  >
                    {{ it.company_name }}
                  </td>
                  <td
                    class="text-center"
                    style="
                      padding-top: 10px;
                      padding-right: 10px;
                      padding-bottom: 10px;
                      padding-left: 10px;
                    "
                  >
                    <span> {{ getProvince(it.visit_province_id) }} </span>
                  </td>
                </tr>
              </table>
            </div>

            <div style="margin-top: 20px">
              <span
                >1. ค่าตอบแทนการนิเทศนักศึกษาสหกิจศึกษา
                (อัตราค่าตอบแทนเหมาจ่ายไม่เกิน
                {{ semesterPDF.semester_visit_expense }} บาท/นักศึกษา 1
                คน)</span
              ><br />
              <span style="padding-left: 15px"
                >ค่าตอบแทนการนิเทศนักศึกษาปฏิบัติงานสหกิจศึกษาจํานวน
                {{ countPDF }} คนรวมเป็นเงิน {{ visit_expense_all }}.00
                บาท</span
              >
            </div>

            <div style="margin-top: 40px">
              <span>2. ค่าเดินทางในการนิเทศนักศึกษาสหกิจศึกษา</span><br />
              <table v-if="travel_array.length != 0">
                <tbody v-for="(it, index) in travel_array" :key="index">
                  <tr v-for="(cp, idx) in it.companies" :key="idx">
                    <td style="width: 400px; padding-left: 30px">
                      {{ cp.name }}
                    </td>
                    <td>
                      จังหวัด
                      {{ getProvince(cp.province_id) }}
                      {{
                        it.price.id == cp.id
                          ? `เหมาจ่าย ${it.price.expense}.00 บาท`
                          : ""
                      }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Absolute Page2 -->
          <span
            style="position: absolute; top: 1240px; left: 50px; font-size: 13px"
            >{{
              supervisor.prefix +
              supervisor.firstname +
              " " +
              supervisor.surname
            }}</span
          >
        </div>
        <div class="html2pdf__page-break" />
        <!-- Page3 -->
        <div
          style="margin-right: 50px; margin-bottom: 50px; margin-left: 50px"
          class="payment-pdf"
        >
          <div class="pdf-page">
            <div class="text-center font-weight-bold" style="margin-top: 49px">
              มหาวิทยาลัยเทคโนโลยีพระจอมเกล้าพระนครเหนือ<br />ใบรับรองการจ่ายเงิน
            </div>

            <div class="" style="margin-top: 20px; font-size: 10px">
              <table class="table-payment">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 110px">
                      วัน เดือน ปี
                    </th>
                    <th class="text-center" style="width: 450px">
                      รายละเอียดรายจ่าย
                    </th>
                    <th class="text-center" style="width: 80px">จำนวนเงิน</th>
                    <th class="text-center" style="width: 150px">หมายเหตุ</th>
                  </tr>
                </thead>
                <tbody v-for="(it, index) in travel_array" :key="index">
                  <tr v-for="(cp, idx) in it.companies2" :key="idx">
                    <td
                      class="text-center"
                      style="
                        padding-top: 5px;
                        padding-bottom: 5px;
                        padding-left: 10px;
                      "
                    >
                      <span>
                        {{ dayjs(it.date).locale("th").format("DD MMM BBBB") }}
                      </span>
                    </td>
                    <td
                      style="
                        padding-top: 5px;
                        padding-right: 10px;
                        padding-bottom: 5px;
                        padding-left: 10px;
                      "
                    >
                      ค่าเดินทางในการนิเทศนักศึกษาสหกิจศึกษา เริ่มต้นที่
                      มจพ.วิทยาเขตระยอง <br />ถีง {{ cp.name }} จังหวัด{{
                        getProvince(cp.province_id)
                      }}
                    </td>
                    <td
                      style="
                        padding-top: 5px;
                        padding-right: 10px;
                        padding-bottom: 5px;
                        padding-left: 10px;
                      "
                      class="text-right"
                    >
                      <!-- {{ it.price.id == cp.id ? `${it.price.expense}.00` : "-" }} -->
                      {{
                        cp.show_price ? `${cp.price.toLocaleString()}.00` : "-"
                      }}
                    </td>
                    <td
                      class="text-center"
                      style="
                        padding-top: 5px;
                        padding-right: 10px;
                        padding-bottom: 5px;
                        padding-left: 10px;
                      "
                    >
                      <span>เหมาจ่าย</span>
                    </td>
                  </tr>
                </tbody>
                <tr>
                  <td colspan="2" class="text-right pa-2">
                    <span class="fw-bold"> รวมทั้งสิ้น </span>
                  </td>
                  <td class="pa-2 text-right">
                    {{ total_travel.toLocaleString() }}.00
                  </td>
                  <td />
                </tr>
                <tr>
                  <td colspan="4" class="pa-2">
                    <span>รวมทั้งสิ้น (ตัวอักษร) </span>
                    <span class="ml-10 ms-10">{{
                      bahttext(total_travel)
                    }}</span>
                  </td>
                </tr>
              </table>
            </div>

            <!--
              <div class="" style="margin-top: 20px">
              <span>รวมทั้งสิ้น (ตัวอักษร) {{ bahttext(total_travel) }}</span>
              </div>
            -->
            <div class="" style="margin-top: 20px; margin-left: 40px">
              ข้าพเจ้า
              <span>{{
                supervisor.prefix +
                supervisor.firstname +
                " " +
                supervisor.surname
              }}</span>
              ตำแหน่ง อาจารย์
            </div>
            <div class="">
              <span>หน่วยงาน คณะบริหารธุรกิจ</span><br />
              <span
                >ขอรับรองว่ารายจ่ายข้างต้นไม่อาจเรียกใบเสร็จรับเงินจากผู้รับได้และข้าพเจ้าได้จ่ายเงินไปในงานของมหาวิทยาลัยจริง</span
              >
            </div>

            <!--  -->

            <div style="margin-top: 20px; margin-left: 300px">
              <span
                >ลงชื่อ...............................................................................</span
              >
              <br />
              <span style="padding-left: 50px; font-size: 13px"
                >(
                {{
                  supervisor.prefix +
                  supervisor.firstname +
                  " " +
                  supervisor.surname
                }}
                )</span
              >
              <br />
              <span style="padding-left: 50px">ตำแหน่งอาจารย์</span>
              <br />
              <span style="padding-left: 50px"
                >วันที่
                {{
                  dayjs(studentListPDF[studentListPDF.length - 1]?.visit_date)
                    .locale("th")
                    .format("DD MMMM BBBB")
                }}</span
              >
            </div>
          </div>

          <!--
            {{
            studentListPDF[studentListPDF.length - 1].visit_date  }}
          -->
        </div>
      </template>
    </Vue3Html2pdf>

    <!--  -->
  </div>
</template>

<style lang="scss">
@import "https://fonts.googleapis.com/css2?family=Sarabun&display=swap";

.payment-pdf {
  font-family: Sarabun, sans-serif;
  font-size: 13px;
}

.table-payment,
.table-payment th,
.table-payment td {
  border: 0.1px solid black;
  border-collapse: collapse;
}

.page-break {
  page-break-before: always;
}

.pdf-page {
  // padding-bottom: 20px;
  background: white;
  // border: 1px solid #ddd;
  // margin-bottom: 20px; /* กำหนดระยะขอบล่างของทุกหน้าให้เป็น 20px */
  // height: 297mm; /* สมมติว่าใช้ขนาด A4 ที่สูง 297 มิลลิเมตร */
  // box-sizing: border-box;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
