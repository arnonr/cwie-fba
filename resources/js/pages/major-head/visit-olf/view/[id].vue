<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import { useCwieDataStore } from "../useCwieDataStore";
import { form_statuses, statusShow, visit_status } from "@/data-constant/data";

import { PDFDocument, rgb } from "pdf-lib";
import fontkit from "@pdf-lib/fontkit";
import "vue3-pdf-app/dist/icons/main.css";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

dayjs.extend(buddhistEra);
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const cwieDataStore = useCwieDataStore();
const teacherData = JSON.parse(localStorage.getItem("teacherData"));
let userData = JSON.parse(localStorage.getItem("userData"));

const item = ref({
  id: null,
  semester_id: null,
  start_date: null,
  end_date: null,
  supervision_id: null,
  student_id: null,
  company_id: null,
  status_id: null,
  co_name: null,
  co_position: null,
  co_tel: null,
  co_email: null,
  request_name: null,
  request_position: null,
  request_document_date: null,
  request_document_number: null,
  max_response_date: null,
  send_document_date: null,
  send_document_number: null,
  response_document_file: null,
  response_send_at: null,
  reject_status_id: null,
  response_province_id: null,
  confirm_response_at: null,
  workplace_province_id: null,
  workplace_amphur_id: null,
  workplace_tumbol_id: null,
  plan_send_at: null,
  plan_accept_at: null,
  advisor_verified_at: null,
  chairman_approved_at: null,
  faculty_confirmed_at: null,
  company_rating: null,
  next_coop: null,
  province_id: null,
  amphur_id: null,
  tumbol_id: null,
  active: 1,
});

const rejectLog = ref({
  comment: "",
  reject_status_id: 2,
  visit_id: null,
  user_id: userData.user_id,
});

const visitActive = ref({
  supervision_id: teacherData.id,
  form_id: null,
  visit_date: null,
  visit_time: null,
  co_name: null,
  co_position: null,
  visit_type: { title: "onsite", value: "onsite" },
  address: null,
  googlemap_url: null,
  province_id: null,
  amphur_id: null,
  tumbol_id: null,
  visit_expense: null,
  travel_expense: null,
  active: 1,
  visit_status: 1,
});

const visit = ref([]);
const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const isDialogConfirmVisible = ref(false);
const isDialogVisible = ref(false);
const isDialogRejectVisible = ref(false);

const chairman = ref({
  prefix: "",
  firstname: "",
  surname: "",
  signature_file: "",
  executive_position: "",
});

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  visit_types: [
    { title: "onsite", value: "onsite" },
    { title: "online", value: "online" },
  ],
});

const fetchProvinces = () => {
  cwieDataStore
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

const fetchAmphurs = (type = 1) => {
  cwieDataStore
    .fetchAmphurs({
      province_id:
        type == 1 ? item.value.province_id : item.value.workplace_province_id,
    })
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.amphurs = response.data.data.map((r) => {
          return { title: r.name_th, value: r.amphur_id };
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

const fetchTumbols = (type = 1) => {
  cwieDataStore
    .fetchTumbols({
      amphur_id:
        type == 1 ? item.value.amphur_id : item.value.workplace_amphur_id,
    })
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.tumbols = response.data.data.map((r) => {
          return { title: r.name_th, value: r.tumbol_id };
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

const fetchStudent = () => {
  cwieDataStore
    .fetchStudents({
      id: route.params.id,
      //   student_code: userData.username.slice(1, userData.username.length),
      includeAll: true,
      // get id self
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        item.value = data[0];
        console.log(item.value);
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchStudent();

const formItem = ref(null);

const fetchForms = () => {
  cwieDataStore
    .fetchForms({
      student_id: route.params.id,
      //   student_id: student.value.id,
      perPage: 1,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      active: 1,
      includeAll: true,
      includeVisit: true,
    })
    .then(async (response) => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data;

        for (let i = 0; i < data.length; i++) {
          await cwieDataStore
            .fetchTeachers({ id: data[i].chairman_id })
            .then((response) => {
              if (response.status === 200) {
                chairman.value = response.data.data[0];
                console.log(chairman.value);
              } else {
                console.log("error");
              }
            })
            .catch((error) => {
              console.error(error);
              isOverlay.value = false;
            });

          await cwieDataStore
            .fetchMajorHeads({
              semester_id: data[i].semester_id,
              major_id: item.value.major_id,
              active: 1,
              includeAll: true,
            })
            .then((res) => {
              data[i].major_head_name = res.data.data[0].teacher_name;
            });
        }

        formItem.value = data[0];

        fetchVisits();

        // time
        // console.log(visit.value);
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchForms();

const fetchVisits = () => {
  cwieDataStore
    .fetchVisits({
      form_id: formItem.value.id,
      //   student_id: student.value.id,
      perPage: 20,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      includeAll: true,
    })
    .then(async (response) => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data;

        visitActive.value = data[0];

        visit.value = data;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

watch(
  () => item.value.province_id,
  (value, oldValue) => {
    if (value != null) {
      fetchAmphurs(2);
      if (oldValue != "") {
        item.value.amphur_id = null;
        item.value.tumbol_id = null;
      }
    }
  }
);

watch(
  () => item.value.amphur_id,
  (value, oldValue) => {
    if (value != null) {
      fetchTumbols(2);
      if (oldValue != "") {
        item.value.tumbol_id = null;
      }
    }
    console.log(value);
  }
);

onMounted(() => {
  window.scrollTo(0, 0);
});

const format = (date) => {
  const day = dayjs(date).locale("th").format("DD");
  const month = dayjs(date).locale("th").format("MMM");
  const year = date.getFullYear() + 543;

  return `${day} ${month} ${year}`;
};

const responseProvinceName = (province_id) => {
  if (province_id) {
    let province_select = selectOptions.value.provinces.find((x) => {
      return x.value == province_id;
    });
    if (province_select) {
      return province_select.title;
    } else {
      return "-";
    }
  } else {
    return "-";
  }
};

const responseAmphurName = (amphur_id) => {
  if (amphur_id) {
    let amphur_select = selectOptions.value.amphurs.find((x) => {
      return x.value == amphur_id;
    });

    if (amphur_select) {
      return amphur_select.title;
    } else {
      return "-";
    }
  } else {
    return "-";
  }
};

const responseTumbolName = (tumbol_id) => {
  if (tumbol_id) {
    let tumbol_select = selectOptions.value.tumbols.find((x) => {
      return x.value == tumbol_id;
    });

    if (tumbol_select) {
      return tumbol_select.title;
    } else {
      return "-";
    }
  } else {
    return "-";
  }
};

const onRejectSubmit = () => {
  if (rejectLog.comment != "") {
    cwieDataStore
      .addVisitRejectLog({
        ...rejectLog.value,
        active: 1,
      })
      .then((response) => {
        if (response.data.message == "success") {
          localStorage.setItem("Rejected", 1);
          nextTick(() => {
            fetchStudent();
            fetchForms();
            snackbarText.value = "Rejected";
            snackbarColor.value = "success";
            isSnackbarVisible.value = true;
            isOverlay.value = false;
            isDialogRejectVisible.value = false;
          });
        } else {
          isOverlay.value = false;
          console.log("error");
        }
      })
      .catch((error) => {
        console.error(error);
      });
  } else {
    snackbarText.value = "โปรดระบุเหตุผล";
    snackbarColor.value = "error";
    isSnackbarVisible.value = true;
    isOverlay.value = false;
  }
};

const onSubmit = () => {
  cwieDataStore
    .visitApprove({
      id: item.value.id,
      status_id: 4,
      chairman_approved_at: dayjs().format("YYYY-MM-DD"),
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1);
        nextTick(() => {
          fetchStudent();
          fetchForms();
          snackbarText.value = "Approved";
          snackbarColor.value = "success";
          isSnackbarVisible.value = true;
          isOverlay.value = false;
          isDialogVisible.value = false;
        });
      } else {
        isOverlay.value = false;
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
    });
};
</script>
<style lang="scss">
.dp__input {
  color: #787878;
}
</style>
<template>
  <div>
    <VCard title="ข้อมูลขอออกนิเทศ (ปัจจุบัน)">
      <VCardItem>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onValidate()"
        >
          <VRow class="mb-1">
            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลการออกนิเทศ</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formItem != null">
              <VRow>
                <VCol cols="12" md="12">
                  <span>ประเภทการออกนิเทศ: </span>
                  <span>
                    {{ visitActive.visit_type }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>วันที่ออกนิเทศ : </span>
                  <span>
                    {{
                      dayjs(visitActive.visit_date)
                        .locale("th")
                        .format("DD MMMM BBBB")
                    }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>เวลาออกนิเทศ : </span>
                  <span>
                    {{ visitActive.visit_time }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>ชื่อ-นามสกุลพี่เลี้ยง : </span>
                  <span>
                    {{ visitActive.co_name }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>ตำแหน่ง : </span>
                  <span>
                    {{ visitActive.co_position }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>สถานะใบขอออกนิเทศ : </span>
                  <span v-if="visitActive.visit_status">
                    <!-- {{ visitActive.visit_status }} -->
                    {{ visit_status[visitActive.visit_status].title }}
                  </span>
                </VCol>

                <VCol cols="12" md="12" class="text-center">
                  <VBtn
                    color="error"
                    :disabled="visitActive.visit_status != 1"
                    @click="
                      () => {
                        rejectLog.visit_id = visitActive.visit_id;
                        isDialogRejectVisible = true;
                      }
                    "
                  >
                    <VIcon
                      size="16"
                      icon="tabler-edit"
                      style="opacity: 1"
                      class="mr-1"
                    ></VIcon>
                    ส่งกลับให้แก้ไข
                  </VBtn>

                  <VBtn
                    class="ml-2"
                    color="success"
                    :disabled="visitActive.visit_status != 1"
                    @click="
                      () => {
                        approve = it;
                        isDialogVisible = true;
                      }
                    "
                  >
                    <VIcon
                      size="16"
                      icon="tabler-file-description"
                      style="opacity: 1"
                      class="mr-1"
                    ></VIcon>
                    อนุมัติ
                  </VBtn>
                </VCol>
              </VRow>
            </VCol>
            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลนักศึกษา</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formItem != null">
              <VRow>
                <VCol cols="12" md="6">
                  <span>ชื่อ-นามสกุล : </span>
                  <span>
                    {{ item.firstname + " " + item.surname }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>รหัสนักศึกษา : </span>
                  <span>
                    {{ item.student_code }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>ปีการศึกษาที่ออกสหกิจ : </span>
                  <span>
                    {{ formItem.semester_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>อาจารย์นิเทศ : </span>
                  <span>
                    {{ formItem.supervision_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(formItem.start_date)
                        .locale("th")
                        .format("DD MMM BBBB")
                    }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(formItem.end_date)
                        .locale("th")
                        .format("DD MMM BBBB")
                    }}
                  </span>
                </VCol>

                <VDivider class="mt-6 mb-6"></VDivider>
              </VRow>
            </VCol>

            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-map-pin" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลสถานประกอบการ</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formItem != null">
              <VRow>
                <VCol cols="12" md="12">
                  <span>สถานประกอบการ : </span>
                  <span>
                    {{ formItem.company_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="12">
                  <span>ที่อยู่ที่ปฏิบัติงาน : </span>
                  <span> {{ formItem.workplace_address }}</span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>จังหวัด : </span>
                  <span>
                    {{
                      responseProvinceName(formItem.workplace_province_id)
                    }}</span
                  >
                </VCol>

                <VCol cols="12" md="4">
                  <span>อำเภอ : </span>
                  <span>
                    {{ responseAmphurName(formItem.workplace_amphur_id) }}
                  </span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>ตำบล : </span>
                  <span>
                    {{ responseTumbolName(formItem.workplace_tumbol_id) }}
                  </span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>ลิงค์แผนที่ : </span>
                  <a
                    v-if="formItem.workplace_googlemap_url"
                    :href="formItem.workplace_googlemap_url"
                    target="_blank"
                  >
                    <span>
                      <VIcon
                        size="16"
                        icon="tabler-map-pin"
                        style="opacity: 1"
                        class="mr-1"
                      />Map</span
                    >
                  </a>
                </VCol>

                <VDivider class="mt-6 mb-6"></VDivider>
              </VRow>
            </VCol>
          </VRow>
        </VForm>
      </VCardItem>
    </VCard>
    <div v-for="(vs, index) in visit" :key="index">
      <VCard
        v-if="vs.visit_id != visitActive.visit_id"
        title="ข้อมูลขอออกนิเทศ (ยกเลิก)"
        class="mt-10"
      >
        <VCardItem>
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onValidate()"
          >
            <VRow class="mb-1">
              <VCol cols="12" md="12" class="d-flex">
                <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                <h4 class="pt-1 pl-1">ข้อมูลการออกนิเทศ</h4>
              </VCol>

              <VCol cols="12" md="12">
                <VRow>
                  <VCol cols="12" md="12">
                    <span>ประเภทการออกนิเทศ: </span>
                    <span>
                      {{ vs.visit_type }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>วันที่ออกนิเทศ : </span>
                    <span>
                      {{
                        dayjs(vs.visit_date).locale("th").format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>เวลาออกนิเทศ : </span>
                    <span>
                      {{ vs.visit_time }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ชื่อ-นามสกุลพี่เลี้ยง : </span>
                    <span>
                      {{ vs.co_name }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>ตำแหน่ง : </span>
                    <span>
                      {{ vs.co_position }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>เหตุผลยกเลิก : </span>
                    <span>{{ vs.cancel_description }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>เอกสารการยกเลิก : </span>
                    <span>-</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>วันที่ยกเลิก : </span>
                    <span>{{
                      vs.cancel_at
                        ? dayjs(vs.cancel_at)
                            .locale("th")
                            .format("DD MMMM BBBB")
                        : ""
                    }}</span>
                  </VCol>
                </VRow>
              </VCol>
              <VDivider class="mt-4 mb-4"></VDivider>
            </VRow>
          </VForm>
        </VCardItem>
      </VCard>
    </div>

    <VOverlay
      v-model="isOverlay"
      contained
      persistent
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>

    <!-- Dialog -->
    <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Are You Sure?">
        <VCardText> ยืนยันการอนุมัติ </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn @click="isDialogVisible = !isDialogVisible" color="error">
            Cancel
          </VBtn>
          <VBtn @click="onSubmit()" color="success"> Approve </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog v-model="isDialogRejectVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogRejectVisible = !isDialogRejectVisible" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มส่งข้อมูลกลับให้แก้ไข">
        <VCardText>
          <VCol cols="12" md="12" class="align-items-center">
            <label class="v-label font-weight-bold" for="comment"
              >ระบุเหตุผล :
            </label>
            <AppTextarea
              id="comment"
              v-model="rejectLog.comment"
              rows="5"
              persistent-placeholder
            />
          </VCol>
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            @click="isDialogRejectVisible = !isDialogRejectVisible"
            color="error"
          >
            Cancel
          </VBtn>
          <VBtn @click="onRejectSubmit()" color="success"> Reject </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
