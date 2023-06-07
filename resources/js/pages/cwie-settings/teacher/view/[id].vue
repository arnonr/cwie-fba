<script setup>
import { useRoute, useRouter } from "vue-router";
import { useTeacherStore } from "../useTeacherStore";

const teacherStore = useTeacherStore();
const route = useRoute();
const router = useRouter();

const item = ref({});

const isOverlay = ref(false);
const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");
const isDialogVisible = ref(false);

if (localStorage.getItem("added") == 1) {
  snackbarText.value = "Added Teacher";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("added");
}

if (localStorage.getItem("updated") == 1) {
  snackbarText.value = "Updated Teacher";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("updated");
}

teacherStore
  .fetchTeacher({
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
  teacherStore
    .deleteTeacher({
      id: id,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("deleted", 1);
        router.push({
          path: "/cwie-settings/teacher",
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
            name: 'cwie-settings-teacher-edit-id',
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

    <VCard title="ข้อมูลอาจารย์">
      <VCardText>
        <VRow class="mt-1 mb-1">
          <VCol cols="12" md="1">
            <span class="font-weight-bold">คำนำหน้า : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.prefix }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">ชื่อ : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.firstname }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">นามสกุล : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.surname }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">คณะ : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.faculty_name }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">ภาควิชา : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.department_name }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">สถานะใช้งาน : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">
              <VChip color="success" v-if="item.user_id"> เข้าใช้งาน</VChip>
              <VChip color="default" v-else> ยังไม่เข้าใช้งาน </VChip>
            </span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">ที่อยู่ตามบัตรประชาชน : </span>
          </VCol>
          <VCol cols="12" md="10">
            <span class="font-italic">{{ item.address }}</span>
          </VCol>

          <VCol cols="12" md="1">
            <span class="font-weight-bold">จังหวัด : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.province_name }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">อำเภอ : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.amphur_name }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">ตำบล : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.tumbol_name }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">โทรศัพท์ : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.tel }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">เมล : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.email }}</span>
          </VCol>

          <VCol cols="12">
            <hr />
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">ลายเซนต์ : </span>
          </VCol>
          <VCol cols="12" md="10">
            <a :href="item.signature_file" target="_blank">
              <VImg
                :src="item.signature_file"
                style="max-width: 300px"
                class="mt-2"
              />
            </a>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>
          <VCol cols="6" md="2">
            <span class="font-weight-bold">สถานะ : </span>
          </VCol>
          <VCol cols="6" md="10">
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
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
