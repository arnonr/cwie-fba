<script setup>
import CompanyEdit from "@/pages/cwie-settings/company/edit/[id].vue";
import CwieDataEdit from "@/pages/student/cwie-data/edit/[id].vue";
import StudentDataEdit from "@/pages/student/personal-data/[id].vue";
import StudentAction from "@/components/student-view/StudentAction.vue";

import { useStudentApproveStore } from "./useStudentApproveStore";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import "sweetalert2/src/sweetalert2.scss";
import { PDFDocument, rgb } from "pdf-lib";
import fontkit from "@pdf-lib/fontkit";

const studentApproveStore = useStudentApproveStore();
const props = defineProps(["user_type", "student_id", "formActive", "isCheck"]);
const emit = defineEmits(["refresh-data", "change-close", "change-close1"]);

const isDialogVisible = ref(false);
const isDialogRejectVisible = ref(false);
const isDialogCompanyVisible = ref(false);
const isDialogCwieDataVisible = ref(false);
const isDialogStudentDataVisible = ref(false);
const router = useRouter();
const isOverlay = ref(false);

let userData = JSON.parse(localStorage.getItem("userData"));

const student = ref({});
const formActive = ref({});
const new_status_id = ref(null);
const date = ref({});
const rejectLog = ref({
  comment: "",
  reject_status_id: "",
  form_id: "",
  user_id: userData.user_id,
});

watch(props, () => {
  if (props.student_id) {
    fetchStudent();
  }
  if (props.formActive != null) {
    if (props.user_type == "teacher") {
      rejectLog.value.reject_status_id = 1;
      new_status_id.value = 3;
      date.value.advisor_verified_at = dayjs().format("YYYY-MM-DD");
    } else if (props.user_type == "major-head") {
      rejectLog.value.reject_status_id = 2;
      new_status_id.value = 4;
      date.value.chairman_approved_at = dayjs().format("YYYY-MM-DD");
    } else if (props.user_type == "supervisor") {
      date.value.plan_accept_at = dayjs().format("YYYY-MM-DD");
      rejectLog.value.reject_status_id = 5;
      new_status_id.value = 13;
    } else if (props.user_type == "chairman") {
    } else if (props.user_type == "staff") {
      // Staff
      if (props.formActive.status_id == 4) {
        date.value.faculty_confirmed_at = dayjs().format("YYYY-MM-DD");
        rejectLog.value.reject_status_id = 3;
        new_status_id.value = 5;
      } else if (props.formActive.status_id == 7) {
        date.value.confirm_response_at = dayjs().format("YYYY-MM-DD");
        rejectLog.value.reject_status_id = 4;
        new_status_id.value = props.formActive.response_result;
      } else {
      }
      formActive.value = props.formActive;
    } else {
    }
  }
});

// Function
const fetchStudent = () => {
  studentApproveStore
    .fetchStudents({
      id: props.student_id,
      includeAll: true,
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        student.value = { ...data[0] };

        student.value.faculty_name = student.value.faculty_name.replace(
          "คณะ",
          ""
        );

        student.value.major_name = student.value.major_name
          ? student.value.major_name.replace("สาขาวิชา", "")
          : "";

        if (
          student.value.id &&
          student.value.prefix_id &&
          student.value.firstname &&
          student.value.surname &&
          student.value.citizen_id &&
          student.value.address &&
          student.value.province_id &&
          student.value.amphur_id &&
          student.value.tumbol_id &&
          student.value.tel &&
          student.value.email &&
          student.value.faculty_id &&
          student.value.class_year &&
          student.value.class_room &&
          student.value.advisor_id &&
          student.value.gpa &&
          student.value.contact1_name &&
          student.value.contact1_relation &&
          student.value.contact1_tel &&
          student.value.blood_group &&
          student.value.emergency_tel &&
          student.value.height &&
          student.value.weight
        ) {
        }

        studentApproveStore
          .fetchMajorHeads({
            semester_id: props.formActive.semester_id,
            major_id: student.value.major_id,
            active: 1,
            includeAll: true,
          })
          .then((res) => {
            formActive.value.major_head_name = res.data.data[0].teacher_name;
          });
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

const onRejectSubmit = () => {
  if (rejectLog.comment != "") {
    studentApproveStore
      .addRejectLog({
        ...rejectLog.value,
        active: 1,
      })
      .then((response) => {
        if (response.data.message == "success") {
          localStorage.setItem("Rejected", 1);
          nextTick(() => {
            isDialogRejectVisible.value = false;
            emit("refresh-data");
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
  studentApproveStore
    .approve({
      id: props.formActive.id,
      status_id: new_status_id.value,
      ...date.value,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1);
        nextTick(() => {
          isDialogVisible.value = false;
          emit("refresh-data");
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

const reload = () => {
  emit("change-close");
  emit("refresh-data");
};

const confirmCancel = (it) => {
  Swal.fire({
    title: "Are you sure?",
    text: "เมื่อยกเลิกการสมัครแล้วจะไม่สามารถแก้ไขข้อมูลได้",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Cancel it!",
    customClass: {
      confirmButton:
        "v-btn v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated",
      cancelButton:
        "v-btn v-btn--elevated v-theme--light bg-error v-btn--density-default v-btn--size-default v-btn--variant-elevated ml-1",
    },
    buttonsStyling: false,
  }).then((result) => {
    if (result.isConfirmed) {
      studentApproveStore
        .editForm({
          id: it.id,
          active: 0,
          status_id: 10,
          //
        })
        .then(async (response) => {
          if (response.status == 200) {
            //
            emit("change-close1");
            emit("refresh-data");
            Swal.fire({
              icon: "success",
              title: "Cancel!",
              text: "Your file has been cancel.",
              customClass: {
                confirmButton:
                  "v-btn v-btn--elevated v-theme--light bg-success v-btn--density-default v-btn--size-default v-btn--variant-elevated",
              },
            });
          } else {
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
        });
    }
  });
};
</script>
<style scoped>
/* .swal2-container {
  z-index: 20001 !important;
} */
</style>
<template>
  <div>
    <div v-if="props.user_type == 'teacher'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.formActive != null"
      >
        <VBtn
          color="error"
          :disabled="props.formActive.status_id != 2"
          @click="
            () => {
              rejectLog.form_id = props.formActive.id;
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
          :disabled="props.formActive.status_id != 2 || props.isCheck == false"
          @click="
            () => {
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
    </div>

    <div v-if="props.user_type == 'major-head'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.formActive != null"
      >
        <VBtn
          color="error"
          :disabled="props.formActive.status_id != 3"
          @click="
            () => {
              rejectLog.form_id = props.formActive.id;
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
          :disabled="props.formActive.status_id != 3"
          @click="
            () => {
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
    </div>

    <div v-if="props.user_type == 'supervisor'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.formActive != null"
      >
        <VBtn
          color="error"
          :disabled="props.formActive.status_id != 12"
          @click="
            () => {
              rejectLog.form_id = props.formActive.id;
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
          :disabled="props.formActive.status_id != 12"
          @click="
            () => {
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
    </div>

    <div v-if="props.user_type == 'staff'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.formActive != null"
      >
        <VBtn
          color="error"
          :disabled="props.formActive.status_id != 4"
          @click="
            () => {
              rejectLog.form_id = props.formActive.id;
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
          :disabled="props.formActive.status_id != 4"
          @click="
            () => {
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
          อนุมัติใบสมัคร
        </VBtn>
      </VCol>

      <!-- รับทราบผลการตอบรับ -->
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.formActive != null"
      >
        <VBtn
          color="error"
          :disabled="props.formActive.status_id != 7"
          @click="
            () => {
              rejectLog.form_id = props.formActive.id;
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
          :disabled="props.formActive.status_id != 7"
          @click="
            () => {
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
          รับทราบผลการตอบรับ
        </VBtn>
      </VCol>

      <VCol cols="12" md="12" class="text-left" v-if="props.formActive != null">
        <!-- @click=" router.push({ path: '/student/cwie-data/edit/' + formActive.id,
        query: { student_id: student.value_id, }, }) " -->

        <VBtn
          color="success"
          class="ml-2"
          @click="isDialogCwieDataVisible = true"
        >
          <!-- :to="{
            path: '/student/cwie-data/edit/' + formActive.id,
            query: {
              student_id: student_id,
            },
          }"
          target="_blank" -->
          แก้ไขข้อมูลใบสมัคร</VBtn
        >

        <VBtn
          color="warning"
          class="ml-2"
          target="_blank"
          @click="isDialogStudentDataVisible = true"
        >
          <!-- :to="{
            path: '/student/personal-data/' + student.value_id,
          }" -->
          แก้ไขข้อมูลทั่วไป</VBtn
        >

        <VBtn
          color="primary"
          class="ml-2"
          @click="isDialogCompanyVisible = true"
        >
          <!-- :to="{
            name: 'cwie-settings-company-edit-id',
            params: { id: formActive.company_id },
          }"
          target="_blank" -->
          แก้ไขข้อมูลสถานประกอบการ
        </VBtn>

        <VBtn color="success" class="ml-2" @click="reload()">
          <VIcon
            size="16"
            icon="tabler-refresh"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
        </VBtn>

        <VBtn
          color="error"
          class="ml-2"
          v-if="formActive"
          @click="confirmCancel(formActive)"
        >
          ยกเลิกการสมัคร
        </VBtn>
      </VCol>

      <VCol cols="12" md="12" class="text-left" v-if="props.formActive != null">
        <!-- Action -->
        <StudentAction
          v-if="props.student_id"
          :student_id="props.student_id"
          :student="student"
          :formActive="formActive"
        />
      </VCol>
    </div>

    <!-- Dialog -->
    <VDialog
      v-model="isDialogVisible"
      persistent
      class="v-dialog-sm"
      style="z-index: 1900 !important"
    >
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

    <VDialog
      v-model="isDialogRejectVisible"
      persistent
      class="v-dialog-sm"
      style="z-index: 1900 !important"
    >
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

    <!-- Edit Company -->
    <VDialog
      v-model="isDialogCompanyVisible"
      persistent
      class="v-dialog-lg"
      style="z-index: 1900 !important"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogCompanyVisible = !isDialogCompanyVisible"
      />

      <!-- Dialog Content -->
      <CompanyEdit
        :fromStudentPage="true"
        :company_id="formActive.company_id"
      ></CompanyEdit>
    </VDialog>

    <!-- Edit Cwie Data -->
    <VDialog
      v-model="isDialogCwieDataVisible"
      persistent
      class="v-dialog-lg"
      style="z-index: 1900 !important; overflow: scroll"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogCwieDataVisible = !isDialogCwieDataVisible"
      />

      <!-- Dialog Content -->
      <CwieDataEdit
        style="overflow: scroll"
        :fromStudentPage="true"
        :form_id="formActive.id"
        :student_id="formActive.student_id"
      ></CwieDataEdit>
    </VDialog>

    <!-- Edit Student Data-->
    <VDialog
      v-model="isDialogStudentDataVisible"
      persistent
      class="v-dialog-lg"
      style="z-index: 1900 !important; overflow: scroll"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogStudentDataVisible = !isDialogStudentDataVisible"
      />

      <!-- Dialog Content -->
      <StudentDataEdit
        style="overflow: scroll"
        :fromStudentPage="true"
        :student_id="formActive.student_id"
      ></StudentDataEdit>
    </VDialog>
  </div>
</template>
