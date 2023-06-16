<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useSemesterStore } from "../useSemesterStore";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";

// const route = useRoute();
dayjs.extend(buddhistEra);
const route = useRoute();
const router = useRouter();
const semesterStore = useSemesterStore();

const item = ref({});

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  teachers: [],
  departments: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

const fetchTeachers = () => {
  semesterStore
    .fetchTeachers()
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.teachers = response.data.data.map((r) => {
          return {
            title: r.prefix + r.firstname + " " + r.surname,
            value: r.id,
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
fetchTeachers();

const fetchDepartments = () => {
  semesterStore
    .fetchDepartments({
      faculty_id: 1,
      name_th: "สาขาวิชา",
    })
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.departments = response.data.data.map((r) => {
          return {
            title: r.name_th,
            value: r.id,
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
fetchDepartments();

semesterStore
  .fetchSemester({
    id: route.params.id,
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

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      console.log(item.value.default_request_doc_date);
      semesterStore
        .editSemester({
          ...item.value,
          start_date:
            item.value.start_date != "" && item.value.start_date != null
              ? dayjs(item.value.start_date).format("YYYY-MM-DD")
              : null,
          end_date:
            item.value.end_date != "" && item.value.end_date != null
              ? dayjs(item.value.end_date).format("YYYY-MM-DD")
              : null,
          regis_start_date:
            item.value.regis_start_date != "" &&
            item.value.regis_start_date != null
              ? dayjs(item.value.regis_start_date).format("YYYY-MM-DD")
              : null,
          regis_end_date:
            item.value.regis_end_date != "" && item.value.regis_end_date != null
              ? dayjs(item.value.regis_end_date).format("YYYY-MM-DD")
              : null,
          default_request_doc_date:
            item.value.default_request_doc_date != "" &&
            item.value.default_request_doc_date != null
              ? dayjs(item.value.default_request_doc_date).format("YYYY-MM-DD")
              : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.push({
                path: "/cwie-settings/semester/view/" + response.data.data.id,
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

const format = (date) => {
  const day = dayjs(date).locale("th").format("DD");
  const month = dayjs(date).locale("th").format("MMM");
  const year = date.getFullYear() + 543;

  return `${day} ${month} ${year}`;
};
</script>
<style lang="scss">
.v-card,
.v-card-item__content {
  overflow: visible !important;
}
.dp__input {
  color: #787878;
}
</style>

<template>
  <div>
    <VCard title="แบบฟอร์มปีการศึกษา">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="term"
                cols="12"
                md="4"
                >ภาคการศึกษาที่ :
              </label>
              <AppTextField
                id="term"
                :rules="[requiredValidator]"
                v-model="item.term"
                placeholder="Term"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="semester_year"
                cols="12"
                md="4"
                >ปีการศึกษา (พ.ศ.) :
              </label>
              <AppTextField
                id="semester_year"
                :rules="[requiredValidator]"
                v-model="item.semester_year"
                placeholder="Year"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="round_no"
                cols="12"
                md="4"
                >รอบการออกสหกิจศึกษาที่ :
              </label>
              <AppTextField
                id="round_no"
                :rules="[requiredValidator]"
                v-model="item.round_no"
                placeholder="Round"
                persistent-placeholder
              />
            </VCol>

            <!--  -->
            <VCol cols="12" md="12">
              <label
                class="v-label font-weight-bold"
                for="chairman_id"
                cols="12"
                md="12"
                >ประธานบริหารโครงการสหกิจศึกษา :
              </label>
              <AppSelect
                :items="selectOptions.teachers"
                v-model="item.chairman_id"
                :rules="[requiredValidator]"
                variant="outlined"
                placeholder="Chairman"
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="start_date"
                cols="12"
                md="4"
                >วันที่เริ่มออกสหกิจศึกษา :
              </label>
              <VueDatePicker
                v-model="item.start_date"
                :enable-time-picker="false"
                locale="th"
                auto-apply
                :format="format"
                :rules="[requiredValidator]"
              >
                <template #year-overlay-value="{ text }">
                  {{ parseInt(text) + 543 }}
                </template>
                <template #year="{ year }">
                  {{ year + 543 }}
                </template>
              </VueDatePicker>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="end_date"
                cols="12"
                md="4"
                >วันที่สิ้นสุดการปฏิบัติสหกิจศึกษา :
              </label>
              <VueDatePicker
                v-model="item.end_date"
                :enable-time-picker="false"
                locale="th"
                auto-apply
                :format="format"
                :rules="[requiredValidator]"
              >
                <template #year-overlay-value="{ text }">
                  {{ parseInt(text) + 543 }}
                </template>
                <template #year="{ year }">
                  {{ year + 543 }}
                </template>
              </VueDatePicker>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="province_id"
                cols="12"
                md="4"
                >วันที่เปิดรับสมัคร :
              </label>
              <VueDatePicker
                v-model="item.regis_start_date"
                :enable-time-picker="false"
                locale="th"
                auto-apply
                :format="format"
                :rules="[requiredValidator]"
              >
                <template #year-overlay-value="{ text }">
                  {{ parseInt(text) + 543 }}
                </template>
                <template #year="{ year }">
                  {{ year + 543 }}
                </template>
              </VueDatePicker>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="province_id"
                cols="12"
                md="4"
                >วันที่ปิดรับสมัคร :
              </label>
              <VueDatePicker
                v-model="item.regis_end_date"
                :enable-time-picker="false"
                locale="th"
                auto-apply
                :format="format"
                :rules="[requiredValidator]"
              >
                <template #year-overlay-value="{ text }">
                  {{ parseInt(text) + 543 }}
                </template>
                <template #year="{ year }">
                  {{ year + 543 }}
                </template>
              </VueDatePicker>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="default_request_doc_no"
                cols="12"
                md="4"
                >เลขที่หนังสือขอความอนุเคราะห์ :
              </label>
              <AppTextField
                id="default_request_doc_no"
                v-model="item.default_request_doc_no"
                placeholder="Year"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="default_request_doc_date"
                cols="12"
                md="4"
                >วันที่ออกหนังสือขอความอนุเคราะห์ :
              </label>
              <VueDatePicker
                v-model="item.default_request_doc_date"
                :enable-time-picker="false"
                locale="th"
                auto-apply
                :format="format"
              >
                <template #year-overlay-value="{ text }">
                  {{ parseInt(text) + 543 }}
                </template>
                <template #year="{ year }">
                  {{ year + 543 }}
                </template>
              </VueDatePicker>
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
