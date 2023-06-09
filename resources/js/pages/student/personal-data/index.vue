<script setup>
import { useRoute, useRouter } from "vue-router";

import {
  blood_groups,
  class_rooms,
  class_years,
  prefix_names,
} from "@/data-constant/data";

import { usePersonalDataStore } from "./usePersonalDataStore";
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const personalDataStore = usePersonalDataStore();

const item = ref({});

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const currentStep = ref(0);
const checkoutSteps = [
  {
    title: "ข้อมูลทั่วไป",
    size: 24,
    icon: "tabler-user",
  },
  {
    title: "ข้อมูลสุขภาพ",
    size: 24,
    icon: "tabler-vaccine",
  },
  {
    title: "เอกสาร",
    size: 24,
    icon: "tabler-file",
  },
];

const checkoutData = ref({
  cartItems: [
    {
      id: 1,
      name: "Google - Google Home - White",
      seller: "Google",
      inStock: true,
      rating: 4,
      price: 299,
      discountPrice: 359,
      quantity: 1,
      estimatedDelivery: "18th Nov 2021",
    },
    {
      id: 2,
      name: "Apple iPhone 11 (64GB, Black)",
      seller: "Apple",
      inStock: true,
      rating: 4,
      price: 899,
      discountPrice: 999,
      quantity: 1,
      estimatedDelivery: "20th Nov 2021",
    },
  ],
  promoCode: "",
  orderAmount: 1198,
  deliveryAddress: "home",
  deliverySpeed: "free",
  deliveryCharges: 0,
  addresses: [
    {
      title: "John Doe (Default)",
      desc: "4135 Parkway Street, Los Angeles, CA, 90017",
      subtitle: "1234567890",
      value: "home",
    },
    {
      title: "ACME Inc.",
      desc: "87 Hoffman Avenue, New York, NY, 10016",
      subtitle: "1234567890",
      value: "office",
    },
  ],
});

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  teachers: [],
  class_years: class_years,
  class_rooms: class_rooms,
  prefix_names: prefix_names,
  blood_groups: blood_groups,
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

const fetchProvinces = () => {
  personalDataStore
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
  personalDataStore
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
  personalDataStore
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

const fetchTeachers = () => {
  personalDataStore
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

// console.log(localStorage.getItem("userData"));

let userData = JSON.parse(localStorage.getItem("userData"));

personalDataStore
  .fetchStudents({
    // id: route.params.id,
    username: userData.username.slice(1, userData.username.length),
    includeAll: true,
    // get id self
  })
  .then((response) => {
    if (response.data.message == "success") {
      const { data } = response.data;
      item.value = { ...data[0] };

      //   item.value.namecard_file_old = null;
      //   if (data.namecard_file != null) {
      //     item.value.namecard_file_old = data.namecard_file;
      //   }
      //   item.value.namecard_file = [];
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
    // console.log(value);
  }
);

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      personalDataStore
        .editPersonalData({
          ...item.value,
          //   namecard_file:
          //     item.value.namecard_file.length !== 0
          //       ? item.value.namecard_file[0]
          //       : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              console.Console("SUCCESS");
              //   router.push({
              //     path:
              //       "/cwie-settings/personalData/view/" + response.data.data.id,
              //   });
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

const e1 = 1;
</script>
<style lang="scss">
.checkout-stepper {
  .stepper-icon-step {
    .step-wrapper + svg {
      margin-inline: 3.5rem !important;
    }
  }
}

// .v-input--disabled {
//   background-color: #eee;
// }
</style>
<template>
  <div>
    <VCard title="ข้อมูลส่วนตัว">
      <VCardText>
        <!-- 👉 Stepper -->
        <AppStepper
          v-model:current-step="currentStep"
          class="checkout-stepper"
          :items="checkoutSteps"
          :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
        />
      </VCardText>

      <VDivider />

      <VCardText>
        <!-- 👉 stepper content -->
        <VWindow v-model="currentStep" class="disable-tab-transition">
          <VWindowItem>
            <VRow>
              <VCol cols="12" md="12" class="d-flex">
                <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                <h4 class="pt-1 pl-1">ข้อมูลทั่วไป</h4>
              </VCol>
              <VCol style="margin-top: -1.5em">
                <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
              </VCol>
            </VRow>

            <VRow>
              <VCol cols="12" md="3" class="align-items-center">
                <label
                  class="v-label font-weight-bold"
                  for="student_code"
                  cols="12"
                  md="4"
                  >รหัสนักศึกษา :
                </label>
                <AppTextField
                  id="student_code"
                  v-model="item.student_code"
                  persistent-placeholder
                  disabled
                />
              </VCol>
              <VCol cols="12" md="3" class="align-items-center">
                <label
                  class="v-label font-weight-bold"
                  for="prefix_id"
                  cols="12"
                  md="2"
                  >คำนำหน้า :
                </label>
                <AppSelect
                  :items="selectOptions.prefix_names"
                  v-model="item.prefix_id"
                  variant="outlined"
                  placeholder="Prefix"
                  clearable
                />
              </VCol>
              <VCol cols="12" md="3" class="align-items-center">
                <label
                  class="v-label font-weight-bold"
                  for="firstname"
                  cols="12"
                  md="4"
                  >ชื่อ :
                </label>
                <AppTextField
                  id="firstname"
                  v-model="item.firstname"
                  persistent-placeholder
                />
              </VCol>
              <VCol cols="12" md="3" class="align-items-center">
                <label
                  class="v-label font-weight-bold"
                  for="surname"
                  cols="12"
                  md="4"
                  >นามสกุล :
                </label>
                <AppTextField
                  id="surname"
                  v-model="item.surname"
                  persistent-placeholder
                />
              </VCol>
              <VCol cols="12" md="6" class="align-items-center">
                <label class="v-label font-weight-bold" for="" cols="12" md="4"
                  >คณะ :
                </label>
                <AppTextField
                  id="faculty_name"
                  v-model="item.faculty_name"
                  persistent-placeholder
                  disabled
                />
              </VCol>
              <VCol cols="12" md="6" class="align-items-center">
                <label class="v-label font-weight-bold" for="" cols="12" md="4"
                  >สาขาวิชา :
                </label>
                <AppTextField
                  id="major_name"
                  v-model="item.major_name"
                  persistent-placeholder
                  disabled
                />
              </VCol>
            </VRow>
            <!--  -->
          </VWindowItem>

          <VWindowItem>
            <!--  -->
            FREEDOM
          </VWindowItem>

          <VWindowItem>
            <!--  -->
            sadasdashgdwq
          </VWindowItem>
        </VWindow>
      </VCardText>
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
