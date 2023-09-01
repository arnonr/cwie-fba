<script setup>
import { useRoute, useRouter } from "vue-router";
import { useCompanyStore } from "../useCompanyStore";

const companyStore = useCompanyStore();
const route = useRoute();
const router = useRouter();

const item = ref({
  id: null,
  name_th: "",
  name_en: "",
  tel: "",
  fax: "",
  email: "",
  website: "",
  blacklist: "",
  comment: "",
  namecard_file: [],
  location_file: [],
  address: "",
  province_id: "",
  amphur_id: "",
  tumbol_id: "",
  active: 1,
});

const isOverlay = ref(false);
const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");
const isDialogVisible = ref(false);

if (localStorage.getItem("added") == 1) {
  snackbarText.value = "Added Company";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("added");
}

if (localStorage.getItem("updated") == 1) {
  snackbarText.value = "Updated Company";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("updated");
}

companyStore
  .fetchCompany({
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
  companyStore
    .deleteCompany({
      id: id,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("deleted", 1);
        router.push({
          path: "/cwie-settings/company",
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
            name: 'cwie-settings-company-edit-id',
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

    <VCard title="ข้อมูลสถานประกอบการ">
      <VCardText>
        <VRow class="mt-1 mb-1">
          <VCol cols="12" md="2">
            <span class="font-weight-bold">ชื่อบริษัท : </span>
          </VCol>
          <VCol cols="12" md="10">
            <span class="font-italic">{{ item.name_th }}</span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">ที่อยู่ : </span>
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
            <span class="font-weight-bold">อำเภอ/เขต : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.amphur_name }}</span>
          </VCol>
          <VCol cols="12" md="1">
            <span class="font-weight-bold">ตำบล/แขวง : </span>
          </VCol>
          <VCol cols="12" md="3">
            <span class="font-italic">{{ item.tumbol_name }}</span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>
          <!--  -->
          <VCol cols="12" md="2">
            <span class="font-weight-bold">โทรศัพท์ : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{ item.tel }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">แฟกซ์ : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{ item.fax }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">เมล : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{ item.email }}</span>
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">เว็บไซต์ : </span>
          </VCol>
          <VCol cols="12" md="4">
            <span class="font-italic">{{ item.website }}</span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">นามบัตร : </span>
          </VCol>
          <VCol cols="12" md="10">
            <a :href="item.namecard_file" target="_blank">
              <VImg
                :src="item.namecard_file"
                style="max-width: 300px"
                class="mt-2"
              />
            </a>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">ภาพ Google MAP : </span>
          </VCol>
          <VCol cols="12" md="10">
            <a :href="item.location_file" target="_blank">
              <VImg
                :src="item.location_file"
                style="max-width: 300px"
                class="mt-2"
              />
            </a>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>
          <VCol cols="12" md="2">
            <span class="font-weight-bold">ความคิดเห็น : </span>
          </VCol>
          <VCol cols="12" md="10">
            <span class="font-italic">{{ item.comment }}</span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>
          <VCol cols="6" md="2">
            <span class="font-weight-bold">Blacklist : </span>
          </VCol>
          <VCol cols="6" md="10">
            <VChip color="error" v-if="item.blacklist == 1"> Blacklist </VChip>
            <VChip color="default" v-else> None </VChip>
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
