<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import { useVisitFormStore } from "./useVisitFormStore";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

dayjs.extend(buddhistEra);
const visitFormStore = useVisitFormStore();
const route = useRoute();
const router = useRouter();

const props = defineProps(["user_type", "student_id"]);
const emit = defineEmits(["refresh-data"]);
const teacherData = JSON.parse(localStorage.getItem("teacherData"));
let userData = JSON.parse(localStorage.getItem("userData"));

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const isDialogConfirmVisible = ref(false);

const student = ref({});

const item = ref({
  supervision_id: teacherData.id,
  form_id: null,
  visit_date: null,
  visit_time: null,
  co_name: null,
  co_position: null,
  visit_type: { title: "onsite", value: "onsite" },
  address: null,
  googlemap_url: null,
  province_id: null,
  amphur_id: null,
  tumbol_id: null,
  visit_expense: null,
  travel_expense: null,
  active: 1,
  visit_status: 1,
});
const formActive = ref(null);
const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  visit_types: [
    { title: "onsite", value: "onsite" },
    { title: "online", value: "online" },
  ],
});

const provinces = ref([]);
const amphurs = ref([]);
const tumbols = ref([]);
// Fetch
const fetchProvinces = () => {
  visitFormStore
    .fetchProvinces({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.provinces = response.data.data.map((r) => {
          return { title: r.name_th, value: r.province_id };
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
fetchProvinces();

const fetchAmphurs = () => {
  visitFormStore
    .fetchAmphurs()
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.amphurs = response.data.data.map((r) => {
          return { title: r.name_th, value: r.amphur_id };
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
fetchAmphurs();

const fetchTumbols = () => {
  visitFormStore
    .fetchTumbols()
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.tumbols = response.data.data.map((r) => {
          return { title: r.name_th, value: r.tumbol_id };
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

const fetchStudent = () => {
  visitFormStore
    .fetchStudents({
      id: props.student_id,
      includeAll: true,
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        student.value = data[0];
        console.log(item.value);
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchStudent();

const fetchForms = () => {
  visitFormStore
    .fetchForms({
      student_id: props.student_id,
      perPage: 1,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      active: 1,
      includeAll: true,
    })
    .then(async (response) => {
      if (response.data.message == "success") {
        const { data } = response.data;

        item.value.form_id = data[0].id;
        item.value.address = data[0].workplace_address;
        item.value.googlemap_url = data[0].workplace_googlemap_url;
        item.value.province_id = data[0].workplace_province_id;
        item.value.amphur_id = data[0].workplace_amphur_id;
        item.value.tumbol_id = data[0].workplace_tumbol_id;

        formActive.value = { ...data[0] };
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};
fetchForms();

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      visitFormStore
        .addVisit({
          ...visit.value,
          visit_type: visit.value.visit_type.value,
          visit_date:
            visit.value.visit_date != "" && visit.value.visit_date != null
              ? dayjs(visit.value.visit_date).format("YYYY-MM-DD")
              : null,
          visit_time: `${visit.value.visit_time.hours}:${visit.value.visit_time.minutes}:${visit.value.visit_time.seconds}`,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("added", 1);
            nextTick(() => {
              isDialogConfirmVisible.value = false;
              emit("refresh-data");
            });
          } else {
            isOverlay.value = false;
            isDialogConfirmVisible.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
        });
    } else {
      isOverlay.value = false;
      isDialogConfirmVisible.value = false;
    }
  });
};

const onValidate = () => {
  // แก้ไข
  refForm.value?.validate().then(({ valid }) => {
    if (!valid) {
      console.log("FREEDOM");
      isOverlay.value = false;
      isDialogConfirmVisible.value = false;
    } else {
      console.log("FREEDOM1");
      isDialogConfirmVisible.value = true;
    }
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

const getProvince = (province_id) => {
  if (province_id == null) return "";
  let res = provinces.value.find((e) => {
    return (e.province_id = province_id);
  });
  return res.name_th;
};

const getAmphur = (amphur_id) => {
  if (amphur_id == null) return "";
  let res = amhpurs.value.find((e) => {
    return (e.amphur_id = amphur_id);
  });
  return res.name_th;
};

const getTumbol = (tumbol_id) => {
  if (tumbol_id == null) return "";
  let res = tumbols.value.find((e) => {
    return (e.tumbol_id = tumbol_id);
  });
  return res.name_th;
};
</script>
<style lang="scss">
.dp__input {
  color: #787878;
}
</style>
<template>
  <div>
    <VCard title="แบบฟอร์มขอออกนิเทศ">
      <VCardItem>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onValidate()"
        >
          <VRow class="mb-1">
            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลการขอออกนิเทศ</h4>
            </VCol>
            <VCol style="margin-top: -1.5em" cols="12" md="12">
              <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
            </VCol>

            <VCol cols="12" md="12" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="end_date"
                cols="12"
                md="4"
                >ประเภทการออกนิเทศ :
              </label>
              <VSelect
                v-model="item.visit_type"
                density="compact"
                variant="outlined"
                clearable
                :items="selectOptions.visit_types"
                style="z-index: 20001 !important; max-height: 200px"
                max-height="200px"
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="end_date"
                cols="12"
                md="4"
                >วันที่ออกนิเทศ :
              </label>
              <VueDatePicker
                v-model="item.visit_date"
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
                >เวลาออกนิเทศ :
              </label>
              <VueDatePicker
                v-model="item.visit_time"
                time-picker
                locale="th"
                auto-apply
                :rules="[requiredValidator]"
              >
              </VueDatePicker>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_name"
                >ชื่อ-สกุล พี่เลี้ยง :
              </label>
              <AppTextField
                id="co_name"
                v-model="item.co_name"
                placeholder="Name"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_position"
                >ตำแหน่ง พี่เลี้ยง :
              </label>
              <AppTextField
                id="co_position"
                v-model="item.co_position"
                placeholder="Position"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_phone"
                >เบอร์ติดต่อ พี่เลี้ยง :
              </label>
              <AppTextField
                id="co_phone"
                v-model="item.co_phone"
                placeholder="phone"
                persistent-placeholder
              />
            </VCol>

            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลนักศึกษา</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formActive != null">
              <VRow>
                <VCol cols="12" md="6">
                  <span>ชื่อ-นามสกุล : </span>
                  <span>
                    {{ student.firstname + " " + student.surname }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>รหัสนักศึกษา : </span>
                  <span>
                    {{ student.student_code }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>ปีการศึกษาที่ออกสหกิจ : </span>
                  <span>
                    {{ formActive.semester_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>อาจารย์นิเทศ : </span>
                  <span>
                    {{ formActive.supervision_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(formActive.start_date)
                        .locale("th")
                        .format("DD MMM BBBB")
                    }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(formActive.end_date)
                        .locale("th")
                        .format("DD MMM BBBB")
                    }}
                  </span>
                </VCol>

                <VDivider class="mt-6 mb-6"></VDivider>
              </VRow>
            </VCol>

            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-map-pin" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลสถานประกอบการ</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formActive != null">
              <VRow>
                <VCol cols="12" md="12">
                  <span>สถานประกอบการ : </span>
                  <span>
                    {{ formActive.company_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="12">
                  <span>ที่อยู่ที่ปฏิบัติงาน : </span>
                  <span> {{ formActive.workplace_address }}</span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>จังหวัด : </span>
                  <span>
                    {{ getProvince(formActive.workplace_province_id) }}</span
                  >
                </VCol>

                <VCol cols="12" md="4">
                  <span>อำเภอ : </span>
                  <span>
                    {{ formActive.workplace_amphur_id }}
                  </span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>ลิงค์แผนที่ : </span>
                  <a
                    v-if="formActive.workplace_googlemap_url"
                    :href="formActive.workplace_googlemap_url"
                    target="_blank"
                  >
                    <span>
                      <VIcon
                        size="16"
                        icon="tabler-map-pin"
                        style="opacity: 1"
                        class="mr-1"
                      />Map</span
                    >
                  </a>
                </VCol>

                <VDivider class="mt-6 mb-6"></VDivider>
              </VRow>
            </VCol>

            <!-- 👉 submit and reset button -->
            <VCol cols="12" md="9" class="d-flex gap-4">
              <VBtn type="submit" color="success"> ส่งข้อมูล</VBtn>
              <span class="text-error"
                >**ตรวจสอบข้อมูลให้ถูกต้องก่อนส่งข้อมูล</span
              >
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

    <VDialog
      v-model="isDialogConfirmVisible"
      persistent
      class="v-dialog-sm"
      style="z-index: 20001 !important"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogConfirmVisible = !isDialogConfirmVisible"
      />

      <!-- Dialog Content -->
      <VCard title="ยืนยันการส่งข้อมูล">
        <VCardText> โปรดตรวจสอบข้อมูลให้ถูกต้องก่อนกดยืนยัน! </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDialogConfirmVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn @click="onSubmit()" color="error"> ส่งข้อมูล </VBtn>
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
