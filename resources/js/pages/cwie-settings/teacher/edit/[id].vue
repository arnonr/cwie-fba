<script setup>
import { useRoute, useRouter } from "vue-router";
import { useTeacherStore } from "../useTeacherStore";
// const route = useRoute();
const route = useRoute();
const router = useRouter();
const teacherStore = useTeacherStore();

const item = ref({
  amphur_id: null,
  faculty_id: null,
  tumbol_id: null,
});

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  departments: [],
  facultys: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

const fetchDepartments = () => {
  teacherStore
    .fetchDepartments({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.departments = response.data.data.map((r) => {
          return { title: r.name_th, value: r.id };
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

const fetchProvinces = () => {
  teacherStore
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
  teacherStore
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
  teacherStore
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

teacherStore
  .fetchTeacher({
    id: route.params.id,
  })
  .then((response) => {
    if (response.data.message == "success") {
      const { data } = response.data;
      item.value = { ...data };

      item.value.signature_file_old = null;
      if (data.signature_file != null) {
        item.value.signature_file_old = data.signature_file;
      }
      item.value.signature_file = [];
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
      teacherStore
        .editTeacher({
          ...item.value,
          signature_file:
            item.value.signature_file.length !== 0
              ? item.value.signature_file[0]
              : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.push({
                path: "/cwie-settings/teacher/view/" + response.data.data.id,
              });
            });
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

<template>
  <div>
    <VCard title="แก้ไขข้อมูลอาจารย์">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="2" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="prefix"
                cols="12"
                md="4"
                >คำนำหน้า :
              </label>
              <AppTextField
                v-model="item.prefix"
                variant="outlined"
                placeholder="Prefix"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="5" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="firstname"
                cols="12"
                md="5"
                >ชื่อ :
              </label>
              <AppTextField
                v-model="item.firstname"
                variant="outlined"
                placeholder="Firstname"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="5" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="surname"
                cols="12"
                md="5"
                >นามสกุล :
              </label>
              <AppTextField
                v-model="item.surname"
                variant="outlined"
                placeholder="Surname"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="5" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="faculty"
                cols="12"
                md="5"
                >คณะ :
              </label>
              <AppTextField
                :value="item.faculty_name"
                variant="outlined"
                disabled
                placeholder="Faculrt"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="5" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="department"
                cols="12"
                md="5"
                >สาขา :
              </label>
              <AppTextField
                :value="item.department_name"
                variant="outlined"
                disabled
                placeholder="Department"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2" class="align-items-center">
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

            <VCol cols="12" md="1">
              <span class="font-weight-bold">ลายเซนต์ : </span>
            </VCol>

            <VCol cols="12" md="9">
              <VFileInput
                label="Upload Signature"
                id="signature_file"
                v-model="item.signature_file"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2" class="pl-2">
              <a
                :href="
                  item.signature_file_old != null
                    ? item.signature_file_old
                    : '/'
                "
                target="_blank"
              >
                <VBtn style="width: 100%"> View File </VBtn></a
              >
            </VCol>

            <VCol cols="12" md="12" class="align-items-center">
              <label
                class="v-label font-weight-bold"
                for="executive_position"
                cols="12"
                md="5"
                >ตำแหน่งบริหาร :
              </label>
              <AppTextField
                v-model="item.executive_position"
                variant="outlined"
                placeholder="Surname"
                persistent-placeholder
              />
            </VCol>

            <!--  -->
            <VCol cols="12">
              <hr />
            </VCol>

            <VCol cols="12" md="12">
              <label class="font-weight-bold">ที่อยู่ตามบัตรประชาชน</label>
            </VCol>

            <VCol cols="12" md="1">
              <label class="font-weight-bold">ที่อยู่ : </label>
            </VCol>
            <VCol cols="12" md="11">
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
              <label class="v-label font-weight-bold" for="mail">เมล : </label>
              <AppTextField
                id="email"
                v-model="item.email"
                placeholder="Email"
                persistent-placeholder
              />
            </VCol>

            <!-- 👉 submit and reset button -->
            <VCol cols="12" md="12" class="d-flex gap-4">
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
