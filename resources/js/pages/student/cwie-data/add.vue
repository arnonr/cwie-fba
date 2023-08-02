<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import CompanyAdd from "../../cwie-settings/company/add.vue";
import { useCwieDataStore } from "./useCwieDataStore";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

dayjs.extend(buddhistEra);
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const cwieDataStore = useCwieDataStore();

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
const companyItem = ref({});
const isNewCompany = ref(true);

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const isDialogAddCompanyVisible = ref(false);
const isDialogConfirmVisible = ref(false);

const setIsDialogAddCompanyVisible = (value) => {
  isDialogAddCompanyVisible.value = value;
};

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  companies: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
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

const fetchAmphurs = () => {
  cwieDataStore
    .fetchAmphurs({
      province_id: item.value.province_id,
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

const fetchTumbols = () => {
  cwieDataStore
    .fetchTumbols({
      amphur_id: item.value.amphur_id,
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

const fetchSemesters = () => {
  cwieDataStore
    .fetchSemesters({})
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

const fetchCompanies = () => {
  cwieDataStore
    .fetchCompanies({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.companies = response.data.data.map((r) => {
          return {
            title: r.name_th,
            value: r.id,
            address: r.address,
            province_id: r.province_id,
            amphur_id: r.amphur_id,
            tumbol_id: r.tumbol_id,
            tel: r.tel,
            fax: r.fax,
            email: r.email,
            website: r.website,
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
fetchCompanies();

let userData = JSON.parse(localStorage.getItem("userData"));
const fetchStudent = () => {
  cwieDataStore
    .fetchStudents({
      // id: route.params.id,
      student_code: userData.username.slice(1, userData.username.length),
      includeAll: true,
      // get id self
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        // student.value = { ...data[0] };

        item.value.student_id = data[0].id;
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

watch(
  () => item.value.province_id,
  (value, oldValue) => {
    if (value != null) {
      fetchAmphurs();
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
      fetchTumbols();
      if (oldValue != "") {
        item.value.tumbol_id = null;
      }
    }
    console.log(value);
  }
);

watch(
  () => item.value.company_id,
  (value) => {
    if (value != null) {
      let check = selectOptions.value.companies.find((c) => {
        return c.value == item.value.company_id;
      });

      if (check) {
        companyItem.value = {
          id: check.value,
          name_th: check.title,
          tel: check.tel,
          fax: check.fax != "null" ? check.fax : "",
          email: check.email != "null" ? check.email : "",
          website: check.website != "null" ? check.website : "",
          address: check.address,
          province_id: check.province_id,
        };

        isNewCompany.value = false;
      } else {
        isNewCompany.value = true;
      }
    } else {
    }
  }
);

watch(
  () => item.value.semester_id,
  (value, oldValue) => {
    if (value != null) {
      let semester = selectOptions.value.semesters.find((x) => {
        return value === x.value;
      });
      item.value.start_date = semester.start_date;
      item.value.end_date = semester.end_date;
    }
    // oldValue
    // Value
  }
);

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      cwieDataStore
        .addForm({
          ...item.value,
          status_id: 2,
          start_date:
            item.value.start_date != "" && item.value.start_date != null
              ? dayjs(item.value.start_date).format("YYYY-MM-DD")
              : null,
          end_date:
            item.value.end_date != "" && item.value.end_date != null
              ? dayjs(item.value.end_date).format("YYYY-MM-DD")
              : null,

          namecard_file:
            item.value.namecard_file.length !== 0
              ? item.value.namecard_file[0]
              : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("added", 1);
            nextTick(() => {
              router.push({
                name: "student-cwie-data",
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
</script>
<style lang="scss">
.dp__input {
  color: #787878;
}
</style>
<template>
  <div>
    <VCard title="แบบฟอร์มสมัครโครงการสหกิจศึกษา">
      <VCardItem>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onValidate()"
        >
          <!-- <VRow> </VRow> -->

          <VRow class="mb-1">
            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลสหกิจศึกษา</h4>
            </VCol>
            <VCol style="margin-top: -1.5em">
              <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
            </VCol>

            <VCol cols="12" md="12">
              <label class="font-weight-bold"
                >ปีการศึกษา/รอบที่ออกสหกิจ :
              </label>
              <AppSelect
                :items="selectOptions.semesters"
                v-model="item.semester_id"
                variant="outlined"
                placeholder="Semester"
                :rules="[requiredValidator]"
                clearable
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
                >วันที่สิ้นสุดการปฏิบัติสหกิจ :
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

            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-map-pin" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลสถานประกอบการ</h4>
            </VCol>
            <!-- <VCol style="margin-top: -1.5em" cols="12" md="12">
              <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
            </VCol> -->

            <VCol cols="12" md="7" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="company_id"
                cols="12"
                md="4"
                >สถานประกอบการ (ค้นหาสถานประกอบการ) :
              </label>
              <!-- <AppSelect variant="outlined" placeholder="Province" clearable /> -->
              <AppAutocomplete
                v-model="item.company_id"
                :items="selectOptions.companies"
                :rules="[requiredValidator]"
                placeholder="ค้นหาชื่อสถานประกอบการ"
                clearable
              />
            </VCol>
            <VCol cols="12" md="5">
              <VBtn
                class="mt-6"
                @click="isDialogAddCompanyVisible = true"
                color="info"
                style="width: 100%"
                >คลิกเพิ่มสถานประกอบการ (ใช้ในกรณีไม่พบชื่อสถานประกอบการ)
              </VBtn>
            </VCol>

            <VCol cols="12" md="8">
              <label
                class="v-label font-weight-bold"
                for="company_id"
                cols="12"
                md="12"
              >
                ที่อยู่ :
              </label>
              <AppTextField
                id="address"
                disabled
                v-model="companyItem.address"
                placeholder="Address"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="province_id"
                cols="12"
                md="4"
                >จังหวัด :
              </label>
              <AppSelect
                :items="selectOptions.provinces"
                v-model="companyItem.province_id"
                disabled
                variant="outlined"
                placeholder="Province"
                clearable
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="tel"
                >โทรศัพท์ :
              </label>
              <AppTextField
                id="tel"
                v-model="companyItem.tel"
                placeholder="Phone"
                disabled
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="fax">แฟกซ์ : </label>
              <AppTextField
                id="fax"
                v-model="companyItem.fax"
                placeholder="Fax"
                disabled
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="mail">เมล : </label>
              <AppTextField
                id="email"
                v-model="companyItem.email"
                disabled
                placeholder="Email"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="website"
                >เว็บไซต์ :
              </label>
              <AppTextField
                id="website"
                v-model="companyItem.website"
                placeholder="Website"
                persistent-placeholder
                disabled
              />
            </VCol>

            <!-- <VCol cols="12" md="12" class="align-items-center">
              <label class="v-label font-weight-bold" for="location_file"
                >นามบัตร :
              </label>
            </VCol>

            <VCol cols="12" md="12" class="align-items-center">
              <label class="v-label font-weight-bold" for="location_file"
                >ภาพ Google Map :
              </label>
            </VCol> -->

            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_name"
                >ชื่อ-สกุล ผู้ประสานงาน :
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
                >ตำแหน่ง ผู้ประสานงาน :
              </label>
              <AppTextField
                id="co_position"
                v-model="item.co_position"
                placeholder="Position"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_tel"
                >เบอร์โทรศัพท์ ผู้ประสานงาน :
              </label>
              <AppTextField
                id="co_tel"
                v-model="item.co_tel"
                placeholder="Tel"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="co_email"
                >เมล ผู้ประสานงาน :
              </label>
              <AppTextField
                id="co_email"
                v-model="item.co_email"
                placeholder="Email"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="12" class="align-items-center">
              <span class="font-weight-bold">นามบัตร : </span>
              <VFileInput
                label="Upload Namecard"
                id="namecard_file"
                v-model="item.namecard_file"
                persistent-placeholder
              />
            </VCol>

            <VDivider class="mt-4 mb-4"></VDivider>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="request_name"
                >ชื่อ-สกุล ผู้เรียนถึง (โปรดระบุคำนำหน้า ชื่อ นามสกุล) :
              </label>
              <AppTextField
                id="request_name"
                v-model="item.request_name"
                placeholder="Name"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="request_position"
                >ตำแหน่ง ผู้เรียนถึง (หนังสือ) :
              </label>
              <AppTextField
                id="request_position"
                v-model="item.request_position"
                placeholder="Position"
                persistent-placeholder
              />
            </VCol>

            <!-- 👉 submit and reset button -->
            <VCol cols="12" md="9" class="d-flex gap-4">
              <VBtn type="submit" color="success"> ส่งข้อมูล</VBtn>
              <span class="text-error"
                >**ตรวจสอบข้อมูลให้ถูกต้องก่อนส่งข้อมูล
                (ไม่สามารถแก้ไขข้อมูลได้ภายหลัง)</span
              >
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

    <!-- Edit Form Dialog -->
    <VDialog v-model="isDialogAddCompanyVisible" persistent class="v-dialog-lg">
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogAddCompanyVisible = !isDialogAddCompanyVisible"
        absolute
      />
      <CompanyAdd
        :isDialogAddCompanyVisible="isDialogAddCompanyVisible"
        :isStudentAdd="true"
        @toggle:isDialogAddCompanyVisible="
          (newValue) => (isDialogAddCompanyVisible = newValue)
        "
        @update:companyItem="
          (newValue) => {
            fetchCompanies();
            companyItem = newValue;
            item.company_id = newValue.id;
            companyItem.province_id = parseInt(newValue.province_id);
          }
        "
      />
    </VDialog>

    <VDialog v-model="isDialogConfirmVisible" persistent class="v-dialog-sm">
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
