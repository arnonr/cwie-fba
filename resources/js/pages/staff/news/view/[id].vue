<script setup>
import { useRoute, useRouter } from "vue-router";
import { useNewsStore } from "../useNewsStore";

const newsStore = useNewsStore();
const route = useRoute();
const router = useRouter();

const item = ref({
  id: null,
  news_title: "",
  news_detail: "",
  news_file: [],
  active: 1,
});

const isOverlay = ref(false);
const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");
const isDialogVisible = ref(false);

if (localStorage.getItem("added") == 1) {
  snackbarText.value = "Added News";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("added");
}

if (localStorage.getItem("updated") == 1) {
  snackbarText.value = "Updated News";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("updated");
}

newsStore
  .fetchNews({
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
  newsStore
    .deleteNews({
      id: id,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("deleted", 1);
        router.push({
          path: "/staff/news",
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
            name: 'staff-news-edit-id',
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

    <VCard title="ข้อมูลข่าว">
      <VCardText>
        <VRow class="mt-1 mb-1">
          <VCol cols="12" md="2">
            <span class="font-weight-bold">ปกข่าว : </span>
          </VCol>
          <VCol cols="12" md="10">
            <a :href="item.news_file" target="_blank">
              <VImg
                :src="item.news_file"
                style="max-width: 300px"
                class="mt-2"
              />
            </a>
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">หัวข้อข่าว: </span>
          </VCol>
          <VCol cols="12" md="10">
            <span class="font-italic">{{ item.news_title }}</span>
          </VCol>
          <VCol cols="12">
            <hr />
          </VCol>

          <VCol cols="12" md="2">
            <span class="font-weight-bold">เนื้อหาข่าว : </span>
          </VCol>
          <VCol cols="12" md="10">
            <span class="font-italic">{{ item.news_detail }}</span>
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

          <VCol cols="6" md="2">
            <span class="font-weight-bold">ปักหมุด : </span>
          </VCol>
          <VCol cols="6" md="10">
            <span class="font-italic">
              <VChip color="success" v-if="item.pinned == 1"> Yes </VChip>
              <VChip color="default" v-else> No </VChip>
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
