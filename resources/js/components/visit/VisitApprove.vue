<script setup>
import { useVisitApproveStore } from "./useVisitApproveStore";
import dayjs from "dayjs";
import "dayjs/locale/th";
import axios from "@axios";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { onMounted } from "vue";

const visitApproveStore = useVisitApproveStore();
const props = defineProps([
  "user_type",
  "student_id",
  "formActive",
  "visitActive",
]);
const emit = defineEmits(["refresh-data"]);

const isDialogVisible = ref(false);
const isDialogRejectVisible = ref(false);
const isDialogEditVisitVisible = ref(false);

let userData = JSON.parse(localStorage.getItem("userData"));

const provinces = ref([]);
const new_visit_status = ref(null);
const visit_province_id = ref(null);
const date = ref({});
const rejectLog = ref({
  comment: "",
  reject_status_id: "",
  visit_id: "",
  user_id: userData.user_id,
});

watch(props, () => {
  if (props.visitActive != null) {
    if (props.user_type == "major-head") {
      rejectLog.value.reject_status_id = 1;
      new_visit_status.value = 2;
      date.value.major_head_approve_at = dayjs().format("YYYY-MM-DD");
    } else if (props.user_type == "chairman") {
      rejectLog.value.reject_status_id = 2;
      new_visit_status.value = 3;
      date.value.chairman_approved_at = dayjs().format("YYYY-MM-DD");
    } else if (props.user_type == "staff") {
      rejectLog.value.reject_status_id = 2;
      //   reject_report_comment
      //   report_status_id
      //   report_status_id.value;
      new_visit_status.value = 6;
      date.value.confirm_report_at = dayjs().format("YYYY-MM-DD");
    } else {
    }
  }
});

const fetchProvince = async () => {
  let res = await axios.get("/province", {
    validateStatus: () => true,
  });

  provinces.value = res.data.data.map((r) => {
    return { title: r.name_th, value: r.province_id };
  });
};
fetchProvince();

// Function
const onRejectSubmit = () => {
  if (rejectLog.value.comment != "") {
    if (props.user_type != "staff") {
      visitApproveStore
        .addRejectLog({
          ...rejectLog.value,
          active: 1,
        })
        .then((response) => {
          if (response.data.message == "success") {
            visitApproveStore
              .editVisit({
                visit_id: rejectLog.value.visit_id,
                visit_reject_status_id: rejectLog.value.reject_status_id,
                active: 1,
              })
              .then((response) => {
                if (response.data.message == "success") {
                  isDialogRejectVisible.value = false;
                  emit("refresh-data");
                }
              });
            localStorage.setItem("Rejected", 1);
            nextTick(() => {});
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
        });
    } else {
      // Staff คือรับทราบรายงานผลการนิเทศ
      console.log(rejectLog.value);
      visitApproveStore
        .editVisit({
          visit_id: rejectLog.value.visit_id,
          reject_report_comment: rejectLog.value.comment,
          report_status_id: 3,
          visit_status: 4,
        })
        .then((response) => {
          if (response.data.message == "success") {
            isDialogRejectVisible.value = false;
            emit("refresh-data");
          }
        })
        .catch((error) => {
          console.error(error);
        });
    }
  } else {
    snackbarText.value = "โปรดระบุเหตุผล";
    snackbarColor.value = "error";
    isSnackbarVisible.value = true;
    isOverlay.value = false;
  }
};

const onSubmit = () => {
  visitApproveStore
    .approve({
      visit_id: props.visitActive.visit_id,
      visit_status: new_visit_status.value,
      ...date.value,
      report_status_id: props.user_type == "staff" ? 2 : undefined,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1);
        nextTick(() => {
          isDialogVisible.value = false;
          emit("refresh-data");
        });
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

const onSubmitVisitProvince = () => {
  visitApproveStore
    .editForm({
      id: props.formActive.id,
      workplace_province_id: visit_province_id.value,
    })
    .then((response) => {
      if (response.data.message == "success") {
        visitApproveStore
          .editVisit({
            visit_id: props.visitActive.visit_id,
            province_id: visit_province_id.value,
          })
          .then((response) => {
            if (response.data.message == "success") {
              localStorage.setItem("Update Success", 1);
              nextTick(() => {
                isDialogEditVisitVisible.value = false;
                emit("refresh-data");
              });
            } else {
              console.log("error");
            }
          })
          .catch((error) => {
            console.error(error);
          });
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
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
    <div v-if="props.user_type == 'major-head'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.visitActive != null"
      >
        <VBtn
          color="error"
          :disabled="
            props.visitActive.visit_status != 1 ||
            props.visitActive.visit_reject_status_id != null
          "
          @click="
            () => {
              rejectLog.visit_id = props.visitActive.visit_id;
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
          :disabled="
            props.visitActive.visit_status != 1 ||
            props.visitActive.visit_reject_status_id != null
          "
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

    <div v-if="props.user_type == 'chairman'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.visitActive != null"
      >
        <VBtn
          color="error"
          :disabled="
            props.visitActive.visit_status != 2 ||
            props.visitActive.visit_reject_status_id != null
          "
          @click="
            () => {
              rejectLog.visit_id = props.visitActive.visit_id;
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
          :disabled="
            props.visitActive.visit_status != 2 ||
            props.visitActive.visit_reject_status_id != null
          "
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
        v-if="props.visitActive != null"
      >
        <VBtn
          class="mr-2"
          color="success"
          @click="
            () => {
              isDialogEditVisitVisible = true;
            }
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          แก้ไขสถานที่ออกนิเทศ
        </VBtn>
        <VBtn
          color="error"
          :disabled="props.visitActive.visit_status != 5"
          @click="
            () => {
              rejectLog.visit_id = props.visitActive.visit_id;
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
          :disabled="props.visitActive.visit_status != 5"
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
          รับทราบผลรายงาน
        </VBtn>
      </VCol>
    </div>

    <!-- Dialog -->
    <VDialog
      v-model="isDialogVisible"
      persistent
      class="v-dialog-sm"
      style="z-index: 20001 !important"
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
      style="z-index: 20001 !important"
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

    <VDialog
      v-model="isDialogEditVisitVisible"
      persistent
      class="v-dialog-sm"
      style="z-index: 2000 !important"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogEditVisitVisible = !isDialogEditVisitVisible"
      />

      <!-- Dialog Content -->
      <VCard title="Are You Sure?">
        <VCardText> แก้ไขข้อมูลสถานที่ออกนิเทศ </VCardText>
        <VCardText>
          <VCol cols="12" md="12" class="align-items-center">
            <label class="v-label font-weight-bold" for="comment"
              >จังหวัด :
            </label>

            <AppSelect
              :items="provinces"
              v-model="visit_province_id"
              variant="outlined"
              placeholder="จังหวัด"
            />
          </VCol>
        </VCardText>
        <!-- <VCardText>
          <VCol cols="12" md="12" class="align-items-center">
            <label class="v-label font-weight-bold" for="comment"
              >อำเภอ :
            </label>
            <AppTextarea
              id="comment"
              v-model="rejectLog.comment"
              rows="5"
              persistent-placeholder
            />
          </VCol>
        </VCardText>
        <VCardText>
          <VCol cols="12" md="12" class="align-items-center">
            <label class="v-label font-weight-bold" for="comment"
              >ตำบล :
            </label>
            <AppTextarea
              id="comment"
              v-model="rejectLog.comment"
              rows="5"
              persistent-placeholder
            />
          </VCol>
        </VCardText> -->
        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            @click="isDialogEditVisitVisible = !isDialogEditVisitVisible"
            color="error"
          >
            Cancel
          </VBtn>
          <VBtn @click="onSubmitVisitProvince()" color="success"> อัพเดท </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
