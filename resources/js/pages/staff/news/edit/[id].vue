<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useNewsStore } from "../useNewsStore";
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const newsStore = useNewsStore();

const item = ref({
  id: null,
  news_title: "",
  news_detail: "",
  news_file: [],
  news_file_old: "",
  pinned: 0,
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
  pins: [
    { title: "Yes", value: 1 },
    { title: "No", value: 0 },
  ],
});

newsStore
  .fetchNews({
    id: route.params.id,
  })
  .then((response) => {
    if (response.data.message == "success") {
      const { data } = response.data;
      item.value = { ...data };

      item.value.news_file_old = null;
      if (data.news_file != null) {
        item.value.news_file_old = data.news_file;
      }
      item.value.news_file = [];
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
      newsStore
        .editNews({
          ...item.value,
          news_file:
            item.value.news_file.length !== 0 ? item.value.news_file[0] : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.push({
                path: "/staff/news/view/" + response.data.data.id,
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
    <VCard title="แก้ไขข่าว">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="2">
              <span class="font-weight-bold">ปกข่าว : </span>
            </VCol>

            <VCol cols="12" md="8">
              <VFileInput
                label="Upload Cover"
                id="news_file"
                v-model="item.news_file"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2" class="pl-2">
              <a
                :href="item.news_file_old != null ? item.news_file_old : '/'"
                target="_blank"
              >
                <VBtn style="width: 100%"> View File </VBtn></a
              >
            </VCol>

            <VCol cols="12" md="2">
              <label class="font-weight-bold">หัวข้อข่าว : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextField
                id="news_title"
                :rules="[requiredValidator]"
                v-model="item.news_title"
                placeholder="Title"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2">
              <label class="font-weight-bold">เนื้อหาข่าว : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextarea
                id="news_detail"
                v-model="item.news_detail"
                placeholder="Detail"
                persistent-placeholder
              />
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

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="pinned"
                >ปักหมุด :
              </label>
              <AppSelect
                :items="selectOptions.pins"
                v-model="item.pinned"
                variant="outlined"
                placeholder="Pin"
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
