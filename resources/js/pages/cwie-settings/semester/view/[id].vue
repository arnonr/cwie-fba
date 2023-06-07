<script setup>
import { useRoute, useRouter } from "vue-router";
import { useSemesterStore } from "../useSemesterStore";
import MajorHead from "./major-head.vue";

import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";

dayjs.extend(buddhistEra);

const semesterStore = useSemesterStore();
const route = useRoute();
const router = useRouter();

const item = ref({});

const isOverlay = ref(false);
const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");
const isDialogVisible = ref(false);

if (localStorage.getItem("added") == 1) {
  snackbarText.value = "Added Semester";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("added");
}

if (localStorage.getItem("updated") == 1) {
  snackbarText.value = "Updated Semester";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("updated");
}

semesterStore
  .fetchSemester({
    id: route.params.id,
  })
  .then((response) => {
    if (response.data.message == "success") {
      item.value = response.data.data;
    } else {
      console.log("error");
    }
  })
  .catch((error) => {
    console.error(error);
    isOverlay.value = false;
  });

const onDelete = (id) => {
  semesterStore
    .deleteSemester({
      id: id,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("deleted", 1);
        router.push({
          path: "/cwie-settings/semester",
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

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>
<style lang="scss">
hr {
  border-top: none;
}
</style>

<template>
  <div>
    <VRow>
      <VCol cols="12" class="mb-2 text-right">
        <VBtn
          color="success"
          prepend-icon="tabler-edit"
          :to="{
            name: 'cwie-settings-semester-edit-id',
            params: { id: route.params.id },
          }"
        >
          EDIT</VBtn
        >
        <VBtn
          prepend-icon="tabler-trash"
          class="ml-2"
          color="error"
          @click="isDialogVisible = true"
        >
          Del
        </VBtn>
      </VCol>
    </VRow>

    <VCard title="ข้อมูลปีการศึกษา">
      <VCardText>
        <VRow class="mt-1 mb-1">
          <VCol cols="12" md="2">
            <span class="font-weight-bold">ปีการศึกษา : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{
              item.term + "/" + item.semester_year
            }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">รอบการออกสหกิจศึกษาที่ : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{ item.round_no }}</span>
          </VCol>

          <!--  -->
          <VCol cols="12" md="2">
            <span class="font-weight-bold">รองคณบดีฝ่ายสหกิจศึกษา : </span>
          </VCol>
          <VCol cols="12" md="10">
            <span class="font-italic">{{ item.chairman_name }}</span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">วันที่เริ่มออกสหกิจศึกษา : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{
              item.start_date != null
                ? dayjs(item.start_date).locale("th").format("DD MMM BB")
                : ""
            }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">วันที่สิ้นสุดสหกิจศึกษา : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{
              item.end_date != null
                ? dayjs(item.end_date).locale("th").format("DD MMM BB")
                : ""
            }}</span>
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">วันที่เปิดรับสมัคร : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{
              item.regis_start_date != null
                ? dayjs(item.regis_start_date).locale("th").format("DD MMM BB")
                : ""
            }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">วันที่ปิดรับสมัคร: </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{
              item.regis_end_date != null
                ? dayjs(item.regis_end_date).locale("th").format("DD MMM BB")
                : ""
            }}</span>
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold"
              >เลขที่หนังสือขอความอนุเคราะห์ :
            </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{ item.default_request_doc_no }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold"
              >วันที่ออกหนังสือขอความอนุเคราะห์:
            </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{
              item.default_request_doc_date != null
                ? dayjs(item.default_request_doc_date)
                    .locale("th")
                    .format("DD MMM BB")
                : ""
            }}</span>
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">สถานะ : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">
              <VChip color="success" v-if="item.active == 1"> Active </VChip>
              <VChip color="default" v-else> In Active </VChip>
            </span>
          </VCol>

          <VCol cols="12">
            <hr />
          </VCol>
        </VRow>
      </VCardText>
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

    <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Are You Sure?">
        <VCardText>
          But you will still be able to retrieve this file.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn @click="onDelete(route.params.id)" color="error"> Delete </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <MajorHead
      class="mt-4"
      :semester_year="item.term + '/' + item.semester_year"
      :semester_id="item.id"
      :title="'dsdsd'"
    />
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
