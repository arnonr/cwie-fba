<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import { useVisitFormStore } from "./useVisitFormStore";

// import Select2 from "vue3-select2-component";
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
  province_name: null,
  is_edit: 0,
});
const formActive = ref(null);
const visitRejectLog = ref([]);
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
        provinces.value = response.data.data.map((r) => {
          return { title: r.name_th, value: r.province_id };
        });
        isOverlay.value = false;
        fetchAmphurs();
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

        amphurs.value = response.data.data.map((r) => {
          return { title: r.name_th, value: r.amphur_id };
        });
        isOverlay.value = false;
        fetchTumbols();
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

const fetchTumbols = () => {
  visitFormStore
    .fetchTumbols()
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.tumbols = response.data.data.map((r) => {
          return { title: r.name_th, value: r.tumbol_id };
        });

        tumbols.value = response.data.data.map((r) => {
          return { title: r.name_th, value: r.tumbol_id };
        });
        isOverlay.value = false;
        fetchStudent();
        fetchForms();
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
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

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
        fetchVisit();
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

const fetchVisit = () => {
  visitFormStore
    .fetchVisits({
      form_id: formActive.id,
      perPage: 1,
      currentPage: 1,
      active: 1,
      orderBy: "active",
      order: "desc",
      includeAll: true,
    })
    .then(async (response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        if (data.length != 0) {
          item.value = data[0];
          item.value.visit_type = {
            title: data[0].visit_type,
            value: data[0].visit_type,
          };

          const timeArray = data[0].visit_time.split(":");
          item.value.visit_time = {
            hours: timeArray[0],
            minutes: timeArray[1],
            seconds: "00",
          };
          item.value.cancel_file = [];
          item.value.cancel_description = "";
          item.value.is_edit = 1;

          fetchVisitRejectLog();
        }
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

const fetchVisitRejectLog = () => {
  visitFormStore
    .fetchVisitRejectLogs({
      visit_id: item.value.visit_id,
    })
    .then(async (response) => {
      if (response.data.message == "success") {
        visitRejectLog.value = response.data.data;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      console.log(item.value);
      visitFormStore
        .addVisit({
          ...item.value,
          visit_type: item.value.visit_type.value,
          visit_date:
            item.value.visit_date != "" && item.value.visit_date != null
              ? dayjs(item.value.visit_date).format("YYYY-MM-DD")
              : null,
          visit_time: `${item.value.visit_time.hours}:${item.value.visit_time.minutes}:${item.value.visit_time.seconds}`,
          visit_id: null,
          cancel_description: null,
          visit_status: 1,
          cancel_file: null,
          cancel_at: null,
          visit_reject_status_id: null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            if (item.value.is_edit == 1) {
              visitFormStore
                .editVisit({
                  active: 0,
                  visit_id: item.value.visit_id,
                  cancel_description: item.value.cancel_description,
                  cancel_at: dayjs().format("YYYY-MM-DD"),
                  cancel_file:
                    item.value.cancel_file.length !== 0
                      ? item.value.cancel_file[0]
                      : null,
                })
                .then((response) => {
                  localStorage.setItem("updated", 1);
                  nextTick(() => {
                    isOverlay.value = false;
                    isDialogConfirmVisible.value = false;
                    emit("refresh-data");
                    //   router.push({
                    //     name: "supervisor-visit",
                    //   });
                  });
                });
            } else {
              isOverlay.value = false;
              isDialogConfirmVisible.value = false;
              emit("refresh-data");
            }
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
      isOverlay.value = false;
      isDialogConfirmVisible.value = false;
    } else {
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

// Province มัน
const getProvince = (province_id) => {
  if (province_id == null || provinces.value.length == 0) return "";
  let res = provinces.value.find((e) => {
    return e.value == province_id;
  });
  return res.title;
};

const getAmphur = (amphur_id) => {
  if (amphur_id == null || amphurs.value.length == 0) return "";
  let res = amphurs.value.find((e) => {
    return e.value == amphur_id;
  });
  return res.title;
};

const getTumbol = (tumbol_id) => {
  if (tumbol_id == null || tumbols.value.length == 0) return "";
  let res = tumbols.value.find((e) => {
    return e.value == tumbol_id;
  });
  return res.title;
};
</script>
<style lang="scss">
.dp__input {
  color: #787878;
}
</style>
<template>
  <div>
    <VCard class="mb-3 pa-5" v-if="visitRejectLog.length != 0">
      <h2>ประวัติการ Reject</h2>
      <div class="text-red" v-for="(rj, index) in visitRejectLog" :key="index">
        วันที่ :
        {{ dayjs(rj.created_at).locale("th").format("DD MMM BB") }}, ผู้ตรวจ :
        {{ rj.reject_status_id == 1 ? "ประธานอาจารย์นิเทศ" : "ประธานบริหาร" }},
        รายละเอียด {{ rj.comment }}
      </div>
    </VCard>

    <VCard title="">
      <VCardItem>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onValidate()"
        >
          <VRow class="mb-1">
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
                style="z-index: 20001 !important"
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

            <VCol
              cols="12"
              md="6"
              class="align-items-center"
              v-if="item.is_edit == 1"
            >
              <label class="v-label font-weight-bold" for="co_phone"
                >ไฟล์หลักฐานการยกเลิก :
              </label>
              <VFileInput
                label="Upload Cancel File"
                id="candel_file"
                v-model="item.cancel_file"
                persistent-placeholder
              />
            </VCol>

            <VCol
              cols="12"
              md="12"
              class="align-items-center"
              v-if="item.is_edit == 1"
            >
              <label class="v-label font-weight-bold" for="cancel_description"
                >เหตุผลการยกเลิก :
              </label>
              <AppTextField
                id="cancel_description"
                v-model="item.cancel_description"
                placeholder="cancel description"
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
                    {{ getAmphur(formActive.workplace_amphur_id) }}
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
        <VCardText v-if="item.is_edit == 1" class="text-error font-weight-bold">
          การแก้ไขข้อมูลต้องอนุมัติใหม่ทั้งหมด!
        </VCardText>

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
