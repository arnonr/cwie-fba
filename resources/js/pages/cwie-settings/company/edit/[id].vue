<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useCompanyStore } from "../useCompanyStore";
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const companyStore = useCompanyStore();

const item = ref({
  id: null,
  name_th: "",
  name_en: "",
  tel: "",
  fax: "",
  email: "",
  website: "",
  blacklist: 0,
  comment: "",
  namecard_file: [],
  location_file: [],
  namecard_file_old: "",
  location_file_old: "",
  address: "",
  province_id: null,
  amphur_id: null,
  tumbol_id: null,
  active: 1,
});

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  blacklists: [
    { title: "No", value: 0 },
    { title: "Yes", value: 1 },
  ],
});

const fetchProvinces = () => {
  companyStore
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
  companyStore
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
  companyStore
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

companyStore
  .fetchCompany({
    id: route.params.id,
  })
  .then((response) => {
    if (response.data.message == "success") {
      const { data } = response.data;
      item.value = { ...data };

      item.value.namecard_file_old = null;
      if (data.namecard_file != null) {
        item.value.namecard_file_old = data.namecard_file;
      }
      item.value.namecard_file = [];

      item.value.location_file_old = null;
      if (data.location_file != null) {
        item.value.location_file_old = data.location_file;
      }
      item.value.location_file = [];
    } else {
      console.log("error");
    }
  })
  .catch((error) => {
    console.error(error);
    isOverlay.value = false;
  });

watch(
  () => item.value.province_id,
  (value, oldValue) => {
    if (value != null) {
      fetchAmphurs();
      if (oldValue != null) {
        item.value.amphur_id = null;
        item.value.tumbol_id = null;
      }
      console.log(item.value.amphur_id);
    }
  }
);

watch(
  () => item.value.amphur_id,
  (value, oldValue) => {
    if (value != null) {
      fetchTumbols();
      if (oldValue != null) {
        item.value.tumbol_id = null;
      }
    }
    console.log(value);
  }
);

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      companyStore
        .editCompany({
          ...item.value,
          namecard_file:
            item.value.namecard_file.length !== 0
              ? item.value.namecard_file[0]
              : null,
          location_file:
            item.value.location_file.length !== 0
              ? item.value.location_file[0]
              : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.push({
                path: "/cwie-settings/company/view/" + response.data.data.id,
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
    <VCard title="แก้ไขสถานประกอบการ">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="2">
              <label class="font-weight-bold">ชื่อบริษัท : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextField
                id="name_th"
                :rules="[requiredValidator]"
                v-model="item.name_th"
                placeholder="Name"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2">
              <label class="font-weight-bold">ที่อยู่ : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextField
                id="address"
                v-model="item.address"
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
                v-model="item.province_id"
                variant="outlined"
                placeholder="Province"
                clearable
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="amphur_id"
                cols="12"
                md="4"
                >อำเภอ/เขต :
              </label>
              <AppSelect
                :items="selectOptions.amphurs"
                v-model="item.amphur_id"
                variant="outlined"
                placeholder="Amphur"
                clearable
              />
            </VCol>

            <VCol cols="12" md="4" class="align-items-center">
              <label class="v-label font-weight-bold" for="tumbol_id"
                >ตำบล/แขวง :
              </label>
              <AppSelect
                :items="selectOptions.tumbols"
                v-model="item.tumbol_id"
                variant="outlined"
                placeholder="Tumbol"
                clearable
              />
            </VCol>

            <!--  -->
            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="tel"
                >โทรศัพท์ :
              </label>
              <AppTextField
                id="tel"
                v-model="item.tel"
                placeholder="Phone"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="fax">แฟกซ์ : </label>
              <AppTextField
                id="fax"
                v-model="item.fax"
                placeholder="Fax"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="mail">เมล : </label>
              <AppTextField
                id="email"
                v-model="item.email"
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
                v-model="item.website"
                placeholder="Website"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2">
              <span class="font-weight-bold">นามบัตร : </span>
            </VCol>

            <VCol cols="12" md="8">
              <VFileInput
                label="Upload Namecard"
                id="namecard_file"
                v-model="item.namecard_file"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2" class="pl-2">
              <a
                :href="
                  item.namecard_file_old != null ? item.namecard_file_old : '/'
                "
                target="_blank"
              >
                <VBtn style="width: 100%"> View File </VBtn></a
              >
            </VCol>

            <VCol cols="12" md="2">
              <span class="font-weight-bold">ภาพ Google Map : </span>
            </VCol>

            <VCol cols="12" md="8">
              <VFileInput
                label="Upload Google Map"
                id="location_file"
                v-model="item.location_file"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2" class="pl-2">
              <a
                :href="
                  item.location_file_old != null ? item.location_file_old : '/'
                "
                target="_blank"
              >
                <VBtn style="width: 100%"> View File </VBtn></a
              >
            </VCol>

            <VCol cols="12" md="2">
              <label class="font-weight-bold">ความคิดเห็น : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextField
                id="comment"
                v-model="item.comment"
                placeholder="Comment"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="mail"
                >Blacklist :
              </label>
              <AppSelect
                :items="selectOptions.blacklists"
                v-model="item.blacklist"
                variant="outlined"
                placeholder="Blacklist"
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
