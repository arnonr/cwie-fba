<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useConfigStore } from "../useConfigStore";

// const route = useRoute();
const route = useRoute();
const router = useRouter();
const configStore = useConfigStore();

const item = ref({});

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  semesters: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

const fetchSemesters = () => {
  configStore
    .fetchSemesters()
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.semesters = response.data.data.map((r) => {
          return {
            title: r.term + "/" + r.semester_year + " รอบที่" + r.round_no,
            value: r.id,
            start_date: r.start_date,
            end_date: r.end_date,
          };
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
fetchSemesters();

configStore
  .fetchConfig({
    id: 1,
    // id: route.params.id,
  })
  .then((response) => {
    if (response.data.message == "success") {
      const { data } = response.data;
      item.value = { ...data };
    } else {
      console.log("error");
    }
  })
  .catch((error) => {
    console.error(error);
    isOverlay.value = false;
  });

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      configStore
        .editConfig({
          ...item.value,
        })
        .then((response) => {
          if (response.data.message == "success") {
            snackbarText.value = "Updated Config";
            snackbarColor.value = "success";
            isSnackbarVisible.value = true;
            //
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
        });
    }
    isOverlay.value = false;
  });
};

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>
<style lang="scss">
.v-card,
.v-card-item__content {
  overflow: visible !important;
}
</style>

<template>
  <div>
    <VCard title="แบบฟอร์มตั้งค่าระบบ">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="term"
                cols="12"
                md="4"
                >email :
              </label>
              <AppTextField
                id="term"
                :rules="[requiredValidator]"
                v-model="item.email"
                placeholder="Email"
                persistent-placeholder
              />
            </VCol>
            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="term"
                cols="12"
                md="4"
                >password :
              </label>
              <AppTextField
                id="term"
                :rules="[requiredValidator]"
                v-model="item.password"
                placeholder="Password Email"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="semester_year"
                cols="12"
                md="4"
                >ตั้งค่าการค้นหาปีการศึกษา (เจ้าหน้าที่) :
              </label>
              <AppSelect
                :items="selectOptions.semesters"
                v-model="item.staff_year_default"
                variant="outlined"
                placeholder="ตั้งค่าการค้นหาปีการศึกษา (เจ้าหน้าที่)"
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="semester_year"
                cols="12"
                md="4"
                >ตั้งค่าการค้นหาปีการศึกษา (อาจารย์ที่ปรึกษา) :
              </label>
              <AppSelect
                :items="selectOptions.semesters"
                v-model="item.teacher_year_default"
                variant="outlined"
                placeholder="ตั้งค่าการค้นหาปีการศึกษา (อาจารย์ที่ปรึกษา)"
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="semester_year"
                cols="12"
                md="4"
                >ตั้งค่าการค้นหาปีการศึกษา (อาจารย์นิเทศ) :
              </label>
              <AppSelect
                :items="selectOptions.semesters"
                v-model="item.supervisor_year_default"
                variant="outlined"
                placeholder="ตั้งค่าการค้นหาปีการศึกษา (อาจารย์นิเทศ)"
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
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
