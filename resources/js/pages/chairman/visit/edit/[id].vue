<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import { useCwieDataStore } from "../useCwieDataStore";
import { form_statuses, statusShow } from "@/data-constant/data";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

dayjs.extend(buddhistEra);
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const cwieDataStore = useCwieDataStore();
const teacherData = JSON.parse(localStorage.getItem("teacherData"));

const item = ref({
  id: null,
  semester_id: null,
  start_date: null,
  end_date: null,
  supervision_id: null,
  student_id: null,
  company_id: null,
  status_id: null,
  co_name: null,
  co_position: null,
  co_tel: null,
  co_email: null,
  request_name: null,
  request_position: null,
  request_document_date: null,
  request_document_number: null,
  max_response_date: null,
  send_document_date: null,
  send_document_number: null,
  response_document_file: null,
  response_send_at: null,
  reject_status_id: null,
  response_province_id: null,
  confirm_response_at: null,
  workplace_province_id: null,
  workplace_amphur_id: null,
  workplace_tumbol_id: null,
  plan_send_at: null,
  plan_accept_at: null,
  advisor_verified_at: null,
  chairman_approved_at: null,
  faculty_confirmed_at: null,
  company_rating: null,
  next_coop: null,
  province_id: null,
  amphur_id: null,
  tumbol_id: null,
  active: 1,
});

const visit = ref({
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

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const isDialogConfirmVisible = ref(false);

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

const fetchProvinces = () => {
  cwieDataStore
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

const fetchAmphurs = (type = 1) => {
  cwieDataStore
    .fetchAmphurs({
      province_id:
        type == 1 ? item.value.province_id : item.value.workplace_province_id,
    })
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

const fetchTumbols = (type = 1) => {
  cwieDataStore
    .fetchTumbols({
      amphur_id:
        type == 1 ? item.value.amphur_id : item.value.workplace_amphur_id,
    })
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

let userData = JSON.parse(localStorage.getItem("userData"));

const fetchStudent = () => {
  cwieDataStore
    .fetchStudents({
      id: route.params.id,
      //   student_code: userData.username.slice(1, userData.username.length),
      includeAll: true,
      // get id self
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        item.value = data[0];
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

const formItem = ref(null);

const fetchForms = () => {
  cwieDataStore
    .fetchForms({
      student_id: route.params.id,
      //   student_id: student.value.id,
      perPage: 1,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      active: 1,
      includeAll: true,
      includeVisit: true,
    })
    .then(async (response) => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data;

        formItem.value = data[0];

        fetchVisits();

        // time
        // console.log(visit.value);
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

const fetchVisits = () => {
  cwieDataStore
    .fetchVisits({
      form_id: formItem.value.id,
      //   student_id: student.value.id,
      perPage: 1,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      active: 1,
      includeAll: true,
    })
    .then(async (response) => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data;

        let visitItem = data[0];

        visit.value.visit_id = visitItem.visit_id;
        visit.value.form_id = visitItem.form_id;
        visit.value.address = visitItem.address;
        visit.value.googlemap_url = visitItem.googlemap_url;
        visit.value.province_id = visitItem.province_id;
        visit.value.amphur_id = visitItem.amphur_id;
        visit.value.tumbol_id = visitItem.tumbol_id;
        visit.value.type = {
          title: visitItem.visit_type,
          value: visitItem.visit_type,
        };
        visit.value.visit_date = visitItem.visit_date;

        // visit.value.visit_time = visitItem.visit_time;
        // console.log(visitItem.visit_time);
        // visit.value.visit_time = dayjs("10:05:10").format("HH:mm:ss");
        // `${visit.value.visit_time.hours}:${visit.value.visit_time.minutes}:${visit.value.visit_time.seconds}`
        const timeArray = visitItem.visit_time.split(":");
        visit.value.visit_time = {
          hours: timeArray[0],
          minutes: timeArray[1],
          seconds: "00",
        };
        visit.value.co_name = visitItem.co_name;
        visit.value.co_position = visitItem.co_position;
        visit.value.co_phone = visitItem.co_phone;

        // visit.value = visitItem;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
};

watch(
  () => item.value.province_id,
  (value, oldValue) => {
    if (value != null) {
      fetchAmphurs(2);
      if (oldValue != "") {
        item.value.amphur_id = null;
        item.value.tumbol_id = null;
      }
    }
  }
);

watch(
  () => item.value.amphur_id,
  (value, oldValue) => {
    if (value != null) {
      fetchTumbols(2);
      if (oldValue != "") {
        item.value.tumbol_id = null;
      }
    }
  }
);

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      cwieDataStore
        .addVisit({
          ...visit.value,
          visit_type: visit.value.visit_type.value,
          visit_date:
            visit.value.visit_date != "" && visit.value.visit_date != null
              ? dayjs(visit.value.visit_date).format("YYYY-MM-DD")
              : null,
          visit_time: `${visit.value.visit_time.hours}:${visit.value.visit_time.minutes}:${visit.value.visit_time.seconds}`,
          visit_id: null,
          cancel_description: null,
          cancel_file: null,
          cancel_at: null,

          //   visit_time: dayjs(visit.value.visit_time).format("HH:mm:ss"),
        })
        .then((response) => {
          if (response.data.message == "success") {
            cwieDataStore
              .editVisit({
                active: 0,
                visit_id: visit.value.visit_id,
                cancel_description: visit.value.cancel_description,
                cancel_at: dayjs().format("YYYY-MM-DD"),
                cancel_file:
                  visit.value.cancel_file.length !== 0
                    ? visit.value.cancel_file[0]
                    : null,
              })
              .then((response) => {
                localStorage.setItem("updated", 1);
                nextTick(() => {
                  router.push({
                    name: "supervisor-visit",
                  });
                });
              });
          } else {
            isOverlay.value = false;
            isDialogConfirmVisible.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
          //   isOverlay.value = false;
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

const responseProvinceName = (province_id) => {
  if (province_id) {
    let province_select = selectOptions.value.provinces.find((x) => {
      return x.value == province_id;
    });
    if (province_select) {
      return province_select.title;
    } else {
      return "-";
    }
  } else {
    return "-";
  }
};

const responseAmphurName = (amphur_id) => {
  if (amphur_id) {
    let amphur_select = selectOptions.value.amphurs.find((x) => {
      return x.value == amphur_id;
    });

    if (amphur_select) {
      return amphur_select.title;
    } else {
      return "-";
    }
  } else {
    return "-";
  }
};

const responseTumbolName = (tumbol_id) => {
  if (tumbol_id) {
    let tumbol_select = selectOptions.value.tumbols.find((x) => {
      return x.value == tumbol_id;
    });

    if (tumbol_select) {
      return tumbol_select.title;
    } else {
      return "-";
    }
  } else {
    return "-";
  }
};
</script>
<style lang="scss">
.dp__input {
  color: #787878;
}
</style>
<template>
  <div>
    <VCard title="แบบฟอร์มเปลี่ยนแปลงข้อมูลขอออกนิเทศ">
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
                >ประเภทการออกนิเทศ* :
              </label>
              <AppSelect
                :items="selectOptions.visit_types"
                v-model="visit.visit_type"
                variant="outlined"
                placeholder="Type"
                :rules="[requiredValidator]"
                clearable
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="end_date"
                cols="12"
                md="4"
                >วันที่ออกนิเทศ* :
              </label>
              <VueDatePicker
                v-model="visit.visit_date"
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
                >เวลาออกนิเทศ* :
              </label>
              <VueDatePicker
                v-model="visit.visit_time"
                :format="'HH:mm:ss'"
                time-picker
                locale="th"
                auto-apply
                :rules="[requiredValidator]"
              >
              </VueDatePicker>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_name"
                >ชื่อ-สกุล พี่เลี้ยง* :
              </label>
              <AppTextField
                id="co_name"
                v-model="visit.co_name"
                placeholder="Name"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_position"
                >ตำแหน่ง พี่เลี้ยง* :
              </label>
              <AppTextField
                id="co_position"
                v-model="visit.co_position"
                placeholder="Position"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_phone"
                >เบอร์ติดต่อ พี่เลี้ยง* :
              </label>
              <AppTextField
                id="co_phone"
                v-model="visit.co_phone"
                placeholder="phone"
                persistent-placeholder
              />
            </VCol>

            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="12" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_phone"
                >เหตุผลการแก้ไข* :
              </label>
              <AppTextField
                id="cancel_description"
                v-model="visit.cancel_description"
                placeholder="เหตุผล"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="12" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_phone"
                >ไฟล์เหตุผล :
              </label>

              <VFileInput
                label="Upload Cancel File"
                id="candel_file"
                v-model="visit.cancel_file"
                persistent-placeholder
              />
              <!-- <AppTextField
                id="co_phone"
                v-model="visit.co_phone"
                placeholder="phone"
                persistent-placeholder
              /> -->
            </VCol>

            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลนักศึกษา</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formItem != null">
              <VRow>
                <VCol cols="12" md="6">
                  <span>ชื่อ-นามสกุล : </span>
                  <span>
                    {{ item.firstname + " " + item.surname }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>รหัสนักศึกษา : </span>
                  <span>
                    {{ item.student_code }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>ปีการศึกษาที่ออกสหกิจ : </span>
                  <span>
                    {{ formItem.semester_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>อาจารย์นิเทศ : </span>
                  <span>
                    {{ formItem.supervision_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(formItem.start_date)
                        .locale("th")
                        .format("DD MMM BBBB")
                    }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                  <span>
                    {{
                      dayjs(formItem.end_date)
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

            <VCol cols="12" md="12" v-if="formItem != null">
              <VRow>
                <VCol cols="12" md="12">
                  <span>สถานประกอบการ : </span>
                  <span>
                    {{ formItem.company_name }}
                  </span>
                </VCol>

                <VCol cols="12" md="12">
                  <span>ที่อยู่ที่ปฏิบัติงาน : </span>
                  <span> {{ formItem.workplace_address }}</span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>จังหวัด : </span>
                  <span>
                    {{
                      responseProvinceName(formItem.workplace_province_id)
                    }}</span
                  >
                </VCol>

                <VCol cols="12" md="4">
                  <span>อำเภอ : </span>
                  <span>
                    {{ responseAmphurName(formItem.workplace_amphur_id) }}
                  </span>
                </VCol>

                <VCol cols="12" md="4">
                  <span>ลิงค์แผนที่ : </span>
                  <a
                    v-if="formItem.workplace_googlemap_url"
                    :href="formItem.workplace_googlemap_url"
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

    <VDialog v-model="isDialogConfirmVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogConfirmVisible = !isDialogConfirmVisible"
      />

      <!-- Dialog Content -->
      <VCard title="ยืนยันการส่งข้อมูล">
        <VCardText>
          โปรดตรวจสอบข้อมูลให้ถูกต้องก่อนกดยืนยัน!

          <br />
          การเปลี่ยนแปลงข้อมูลจะต้องเริ่มอนุมัติใหม่ทั้งหมด
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
