<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useDocumentDownloadStore } from "../useDocumentDownloadStore";
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const documentDownloadStore = useDocumentDownloadStore();

const item = ref({
  id: null,
  document_name: "",
  document_file: [],
  document_file_old: "",
  active: 1,
});

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

documentDownloadStore
  .fetchDocumentDownload({
    id: route.params.id,
  })
  .then((response) => {
    if (response.data.message == "success") {
      const { data } = response.data;
      item.value = { ...data };

      item.value.document_file_old = null;
      if (data.document_file != null) {
        item.value.document_file_old = data.document_file;
      }
      item.value.document_file = [];
    } else {
      console.log("error");
    }
  })
  .catch((error) => {
    console.error(error);
    isOverlay.value = false;
  });

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      documentDownloadStore
        .editDocumentDownload({
          ...item.value,
          document_file:
            item.value.document_file.length !== 0
              ? item.value.document_file[0]
              : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.push({
                path: "/staff/document/view/" + response.data.data.id,
              });
            });
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
          //   isOverlay.value = false;
        });
    }
    isOverlay.value = false;
  });
};

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<template>
  <div>
    <VCard title="แก้ไขเอกสาร">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="2">
              <label class="font-weight-bold">เอกสาร : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextField
                id="document_name"
                :rules="[requiredValidator]"
                v-model="item.document_name"
                placeholder="Name"
                persistent-placeholder
              />
            </VCol>
            <!--  -->
            <VCol cols="12" md="2">
              <span class="font-weight-bold">เอกสาร : </span>
            </VCol>

            <VCol cols="12" md="8">
              <VFileInput
                label="Upload"
                id="upload_file"
                v-model="item.document_file"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2" class="pl-2">
              <a
                :href="
                  item.document_file_old != null ? item.document_file_old : '/'
                "
                target="_blank"
              >
                <VBtn style="width: 100%"> View File </VBtn></a
              >
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="website"
                >สถานะ :
              </label>
              <AppSelect
                :items="selectOptions.actives"
                v-model="item.active"
                variant="outlined"
                placeholder="Status"
              />
            </VCol>

            <!-- 👉 submit and reset button -->
            <VCol cols="12" md="9" class="d-flex gap-4">
              <VBtn type="submit" color="success"> Submit </VBtn>
              <!-- <VBtn color="secondary" variant="tonal" type="reset">
                      Reset
                    </VBtn> -->
            </VCol>
            <!--  -->
          </VRow>
        </VForm>
      </VCardItem>
    </VCard>

    <VOverlay
      v-model="isOverlay"
      contained
      persistent
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
