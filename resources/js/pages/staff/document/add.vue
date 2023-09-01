<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useDocumentDownloadStore } from "./useDocumentDownloadStore";

const route = useRoute();
const router = useRouter();
const documentDownloadStore = useDocumentDownloadStore();
const props = defineProps(["isDialogAddDocumentDownloadVisible"]);
const emit = defineEmits([
  "toggle:isDialogAddDocumentDownloadVisible",
  "update:documentItem",
]);

const item = ref({
  id: null,
  document_name: "",
  document_type_id: 1,
  news_file: [],
  active: 1,
});

let userData = JSON.parse(localStorage.getItem("userData"));

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      documentDownloadStore
        .addDocumentDownload({
          ...item.value,
          document_file:
            item.value.document_file.length !== 0
              ? item.value.document_file[0]
              : null,
          news_file:
            item.value.document_file.length !== 0
              ? item.value.document_file[0]
              : null,
          document_type_id: 1,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("added", 1);
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
    <VCard title="เพิ่มเอกสาร">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="2">
              <label class="font-weight-bold">หัวข้อเอกสาร : </label>
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

            <VCol cols="12" md="2">
              <span class="font-weight-bold">ไฟล์เอกสาร : </span>
            </VCol>

            <VCol cols="12" md="10">
              <VFileInput
                label="Upload"
                id="document_file"
                v-model="item.document_file"
                persistent-placeholder
              />
            </VCol>
            <!-- 👉 submit and reset button -->
            <VCol cols="12" md="9" class="d-flex gap-4">
              <VBtn type="submit" color="success"> Submit </VBtn>
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
