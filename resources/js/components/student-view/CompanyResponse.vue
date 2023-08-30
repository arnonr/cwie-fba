<script setup>
import { useStudentActionStore } from "./useStudentActionStore";
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";

const studentActionStore = useStudentActionStore();
const props = defineProps([
  //   "student_id",
  //   "formActive",
  //   "student",
  "response_company",
]);

const isDialogResponseVisible = ref(false);
const router = useRouter();
const isOverlay = ref(false);
const isResponseFormValid = ref(false);
const refResponseForm = ref(null);

let userData = JSON.parse(localStorage.getItem("userData"));

watch(props, async () => {});

// Function
const onResponseSubmit = () => {
  isOverlay.value = true;
  refResponseForm.value?.validate().then(({ valid }) => {
    if (valid) {
      studentActionStore
        .addResponseBook({
          ...props.response_company.value,
          id: response_company.value.form_id,
          response_document_file:
            response_company.value.response_document_file.length !== 0
              ? response_company.value.response_document_file[0]
              : null,
          response_send_at: dayjs().format("YYYY-MM-DD"),
          status_id: 7,
          response_result: response_company.value.result,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.go({ name: "student-cwie-data" });
              isDialogResponseVisible.value = false;
              snackbarText.value = "Success";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;
            });
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
          isOverlay.value = false;
        });
    } else {
      snackbarText.value = "ข้อมูลไม่ครบถ้วน";
      snackbarColor.value = "error";
      isSnackbarVisible.value = true;
    }
    isOverlay.value = false;
  });
};
</script>
<style lang="scss">
/* .swal2-container {
  z-index: 20001 !important;
} */

.v-select .v-field .v-text-field__prefix,
.v-select .v-field .v-text-field__suffix,
.v-select .v-field .v-field__input,
.v-select .v-field.v-field {
  z-index: 99999999999;
}
</style>
<template>
  <div>
    <VCardItem>
      <VForm
        ref="refResponseForm"
        v-model="isResponseFormValid"
        @submit.prevent="onResponseSubmit"
      >
        <VRow>
          <VCol cols="12">
            <!--  -->
            <label class="v-label" for="status_id">
              ผลการตอบกลับจากสถานประกอบการ1
            </label>
            <v-select
              label="Select"
              :items="[
                'California',
                'Colorado',
                'Florida',
                'Georgia',
                'Texas',
                'Wyoming',
              ]"
            ></v-select>
            <select name="cars" id="cars">
              <option value="volvo">Volvo</option>
              <option value="saab">Saab</option>
              <option value="mercedes">Mercedes</option>
              <option value="audi">Audi</option>
            </select>
            <VSelect
              :items="[
                { title: 'ตอบรับ', value: 8 },
                { title: 'ปฏิเสธ', value: 9 },
                { title: 'สละสิทธิ์', value: 10 },
              ]"
              v-model="props.response_company.result"
              :rules="[requiredValidator]"
              variant="outlined"
              placeholder="Status"
              clearable
              style="z-index: 200000001 !important"
            />
            <!--  -->
          </VCol>
        </VRow>
      </VForm>
    </VCardItem>

    <VCardText class="d-flex justify-end flex-wrap gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        @click="isDialogResponseVisible = false"
      >
        Cancel
      </VBtn>
      <VBtn type="submit" @click="onResponseSubmit" id="btn-submit">
        <span>Save</span></VBtn
      >
    </VCardText>
  </div>
</template>
