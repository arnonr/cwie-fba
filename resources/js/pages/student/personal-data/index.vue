<script setup>
import { requiredValidator } from "@validators";
import Swal from "sweetalert2";
import { useRoute, useRouter } from "vue-router";

import "sweetalert2/src/sweetalert2.scss";

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

const documents_certificate = ref([
  {
    id: null,
    document_file: null,
    document_file_old: ref(null),
    document_type_id: 1,
    document_name: null,
    student_id: null,
  },
  {
    id: null,
    document_file: null,
    document_file_old: ref(null),
    document_type_id: 1,
    document_name: null,
    student_id: null,
  },
]);

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const currentStep = ref(0);
const disabled = ref(false);

const studentSteps = [
  {
    title: "ข้อมูลทั่วไป",
    size: 24,
    icon: "tabler-user",
  },
  {
    title: "ข้อมูลสุขภาพ",
    size: 24,
    icon: "tabler-heart",
  },
  {
    title: "เอกสาร",
    size: 24,
    icon: "tabler-books",
  },
];

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  teachers: [],
  document_types: [],
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

const fetchDocumentTypes = () => {
  personalDataStore
    .fetchDocumentTypes({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.document_types = response.data.data.map((r) => {
          return { title: r.name, value: r.id };
        });

        selectOptions.value.document_types =
          selectOptions.value.document_types.filter((d) => {
            return d.value != 1;
          });

        fetchStudent();

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

const fetchStudentDocuments = () => {
  personalDataStore
    .fetchStudentDocuments({
      student_id: item.value.id,
    })
    .then((response) => {
      if (response.status === 200) {
        const { data } = response.data;

        let document = data.filter((d) => {
          return d.document_type_id != 1;
        });

        item.value.documents = item.value.documents.map((d) => {
          let index = document.find((e) => {
            return d.document_type_id == e.document_type_id;
          });
          if (index) {
            d.id = index.id;
            d.document_file_old = index.document_file;
            d.document_file = [];
          }

          return d;
        });

        // Cert
        let document_cert = data.filter((d) => {
          return d.document_type_id == 1;
        });

        if (document_cert.length > 0) {
          documents_certificate.value = [];

          for (let index = 0; index < document_cert.length; index++) {
            let d = document_cert[index];
            documents_certificate.value.push({
              id: d.id,
              document_type_id: 1,
              document_name: d.document_name,
              student_id: d.student_id,
              document_file_old:
                d.document_file != null ? d.document_file : null,
              document_file: [],
            });
          }
        }

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
  personalDataStore
    .fetchStudents({
      // id: route.params.id,
      student_code: userData.username.slice(1, userData.username.length),
      includeAll: true,

      // get id self
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;

        item.value = { ...data[0] };

        if (
          item.value.status_id < 2 ||
          item.value.status_id == 10 ||
          item.value.status_id == 9
        ) {
          disabled.value = true;
        }

        // item.value.photo_file = [];
        item.value.photo_file_old = null;
        if (data[0].photo_file != null) {
          item.value.photo_file_old = data[0].photo_file;
        }
        console.log(data[0].photo_file);
        item.value.photo_file = [];

        item.value.documents = selectOptions.value.document_types.map((d) => {
          return {
            id: null,
            document_file: null,
            document_file_old: ref(null),
            document_type_id: d.value,
            document_name: d.title,
            student_id: item.value.id,
          };
        });
        fetchStudentDocuments();
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
        .editStudent({
          ...item.value,
          photo_file:
            item.value.photo_file.length !== 0
              ? item.value.photo_file[0]
              : null,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);

            onStudentDocumentSubmit();

            nextTick(() => {
              console.log("Success");
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
    } else {
      snackbarText.value = "ข้อมูลไม่ครบถ้วน";
      snackbarColor.value = "error";
      isSnackbarVisible.value = true;
    }
    isOverlay.value = false;
  });
};

const onStudentDocumentCertificateSubmit = async () => {
  for (let index = 0; index < documents_certificate.value.length; index++) {
    const d = documents_certificate.value[index];

    if (d.document_name != null && d.document_file != null) {
      if (d.document_file.length !== 0) {
        let dataSend = {
          student_id: item.value.id,
          document_type_id: d.document_type_id,
          document_name: d.document_name,
          document_file:
            d.document_file.length !== 0 ? d.document_file[0] : null,
        };

        if (d.id == null) {
          await personalDataStore
            .addStudentDocument(dataSend)
            .then((response) => {
              if (response.status == 200) {
              } else {
                console.log("error");
              }
            })
            .catch((error) => {
              console.error(error);
            });
        } else {
          dataSend["id"] = d.id;
          await personalDataStore
            .editStudentDocument(dataSend)
            .then((response) => {
              if (response.status == 200) {
              } else {
                console.log("error");
              }
            })
            .catch((error) => {
              console.log(error);
            });
        }
      }
    }
  }
};

const onStudentDocumentSubmit = async () => {
  isOverlay.value = true;

  // Document
  for (let index = 0; index < item.value.documents.length; index++) {
    const d = item.value.documents[index];
    if (d.document_file != null) {
      if (d.document_file.length !== 0) {
        let dataSend = {
          student_id: item.value.id,
          document_file:
            d.document_file.length !== 0 ? d.document_file[0] : null,
          document_type_id: d.document_type_id,
          document_name: d.document_name,
        };

        if (d.id == null) {
          await personalDataStore
            .addStudentDocument(dataSend)
            .then((response) => {
              if (response.status == 200) {
                // let { data } = response;
              } else {
                console.log("error");
              }
            })
            .catch((error) => {
              console.error(error);
            });
        } else {
          dataSend["id"] = d.id;
          await personalDataStore
            .editStudentDocument(dataSend)
            .then((response) => {
              if (response.status == 200) {
                // let { data } = response;
                // resolve(true);
              } else {
                console.log("error");
              }
            })
            .catch((error) => {
              console.log(error);
            });
        }
      }
    }
  }

  // Cert
  await onStudentDocumentCertificateSubmit();

  isOverlay.value = false;
  Swal.fire({
    title: "Success",
    text: "บันทึกข้อมูลส่วนตัวเสร็จสิ้น",
    icon: "success",
    confirmButtonText: "Ok",
    customClass: {
      confirmButton:
        "v-btn v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated",
    },
  }).then(async () => {
    isOverlay.value = false;
    // router.push({ name: "student-cwie-data" });
    console.log(item.value.id);
    await personalDataStore
      .fetchForms({ student_id: item.value.id })
      .then((response) => {
        if (response.status == 200) {
          if (response.data.data.length == 0) {
            router.push({ name: "student-cwie-data-add" });
          } else {
            console.log(response.data.data);
            router.push({
              name: "student-cwie-data-edit-id",
              params: { id: response.data.data[0].id },
            });
          }
        } else {
          console.log("error");
        }
      })
      .catch((error) => {
        console.error(error);
      });
  });
};

const removeCertificateItem = (index) => {
  if (documents_certificate.value[index].id != null) {
    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, delete it!",
      customClass: {
        confirmButton:
          "v-btn v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated",
        cancelButton:
          "v-btn v-btn--elevated v-theme--light bg-error v-btn--density-default v-btn--size-default v-btn--variant-elevated ml-1",
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        isOverlay.value = true;

        personalDataStore
          .deleteStudentDocument({
            id: documents_certificate.value[index].id,
          })
          .then(async (response) => {
            if (response.status == 204) {
              documents_certificate.value.splice(index, 1);

              isOverlay.value = false;

              Swal.fire({
                icon: "success",
                title: "Deleted!",
                text: "Your file has been deleted.",
                customClass: {
                  confirmButton:
                    "v-btn v-btn--elevated v-theme--light bg-success v-btn--density-default v-btn--size-default v-btn--variant-elevated",
                },
              });
            } else {
              console.log("error");
              isOverlay.value = false;
            }
          })
          .catch((error) => {
            console.error(error);
            isOverlay.value = false;
          });
      }
    });
  } else {
    documents_certificate.value.splice(index, 1);
  }
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchProvinces();
  fetchTeachers();
  fetchDocumentTypes();
});

const nextTodoId = ref(1);

const repeateAgain = () => {
  documents_certificate.value.push({
    id: (nextTodoId.value += nextTodoId.value),
    id: null,
    document_file: null,
    document_file_old: ref(null),
    document_type_id: 1,
    document_name: null,
    student_id: null,
  });

  // this.$nextTick(() => {
  //   this.trAddHeight(this.$refs.row[0].offsetHeight);
  // });
};
</script>

<template>
  <div>
    <VCard title="ข้อมูลส่วนตัว">
      <VCardText>
        <!-- 👉 Stepper -->
        <AppStepper
          v-model:current-step="currentStep"
          class="checkout-stepper"
          :items="studentSteps"
          :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
        />
      </VCardText>

      <VDivider />

      <VCardText>
        <!-- 👉 stepper content -->
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VWindow v-model="currentStep" class="disable-tab-transition">
            <!-- ข้อมูลทั่วไป -->
            <VWindowItem>
              <VRow>
                <VCol cols="12" md="12" class="d-flex">
                  <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                  <h4 class="pt-1">ข้อมูลทั่วไป</h4>
                </VCol>
                <VCol class="note">
                  <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
                </VCol>
              </VRow>

              <VRow>
                <!--  -->
                <VCol cols="12" md="10">
                  <label class="v-label font-weight-bold" for="document_name"
                    >รูปภาพนักศึกษา (1x1.5 นิ้ว ไฟล์รูปภาพ PNG, JPG)
                  </label>
                  <VFileInput
                    v-model="item.photo_file"
                    label="Upload File"
                    persistent-placeholder
                    :disabled="!disabled"
                  />
                </VCol>

                <VCol cols="12" md="2" class="pl-2 align-self-end">
                  <VBtn
                    style="width: 100%"
                    :color="
                      item.photo_file_old == null ? 'secondary' : 'primary'
                    "
                    :disabled="item.photo_file_old == null"
                    :href="
                      item.photo_file_old != null ? item.photo_file_old : '/'
                    "
                    target="_blank"
                  >
                    View File
                  </VBtn>
                </VCol>
                <!--  -->

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
                    v-model="item.prefix_id"
                    :items="selectOptions.prefix_names"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    placeholder="Prefix"
                    :disabled="!disabled"
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
                    :rules="[requiredValidator]"
                    :disabled="!disabled"
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
                    :rules="[requiredValidator]"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>
                <VCol cols="12" md="6" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for=""
                    cols="12"
                    md="4"
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
                  <label
                    class="v-label font-weight-bold"
                    for=""
                    cols="12"
                    md="4"
                    >สาขาวิชา :
                  </label>
                  <AppTextField
                    id="major_name"
                    v-model="item.major_name"
                    persistent-placeholder
                    disabled
                  />
                </VCol>

                <VCol cols="12" md="2" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="class_year"
                    cols="12"
                    md="2"
                    >ชั้นปีที่ :
                  </label>
                  <AppSelect
                    v-model="item.class_year"
                    :items="selectOptions.class_years"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    placeholder="Class Year"
                    :disabled="!disabled"
                    clearable
                  />
                </VCol>

                <VCol cols="12" md="2" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="class_room"
                    cols="12"
                    md="2"
                    >ห้อง :
                  </label>
                  <AppSelect
                    v-model="item.class_room"
                    :items="selectOptions.class_rooms"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    :disabled="!disabled"
                    placeholder="Class Room"
                    clearable
                  />
                </VCol>
                <VCol cols="12" md="6" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="advisor"
                    cols="12"
                    md="2"
                    >อาจารย์ที่ปรึกษา :
                  </label>
                  <AppAutocomplete
                    v-model="item.advisor_id"
                    :items="selectOptions.teachers"
                    :rules="[requiredValidator]"
                    placeholder="Advisor"
                    :disabled="!disabled"
                    clearable
                  />

                  <!--
                    <AppSelect
                    :items="selectOptions.teachers"
                    v-model="item.advisor_id"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    :disabled="!disabled"
                    placeholder="Advisor"
                    clearable
                    /> 
                  -->
                </VCol>

                <VCol cols="12" md="2" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="GPA"
                    cols="12"
                    md="2"
                    >เกรดเฉลี่ย
                  </label>
                  <AppTextField
                    id="GPA"
                    v-model="item.gpa"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                    :disabled="!disabled"
                    type="number"
                  />
                </VCol>

                <VDivider class="mt-4 mb-4" />

                <VCol cols="12" md="12" class="d-flex">
                  <VIcon size="22" icon="tabler-map-pin" style="opacity: 1" />
                  <h4 class="pt-1 pl-3 pb-2">ที่อยู่</h4>
                </VCol>

                <VCol cols="12" md="12" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="GPA"
                    cols="12"
                    md="2"
                    >ที่อยู่ปัจจุบัน
                  </label>
                  <AppTextarea
                    id="address"
                    v-model="item.address"
                    :rules="[requiredValidator]"
                    :disabled="!disabled"
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
                    v-model="item.province_id"
                    :items="selectOptions.provinces"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    :disabled="!disabled"
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
                    v-model="item.amphur_id"
                    :items="selectOptions.amphurs"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
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
                    v-model="item.tumbol_id"
                    :items="selectOptions.tumbols"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    :disabled="!disabled"
                    placeholder="Tumbol"
                    clearable
                  />
                </VCol>

                <VCol cols="12" md="6" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="tel"
                    cols="12"
                    md="2"
                    >เบอร์โทรศัพท์
                  </label>
                  <AppTextField
                    id="tel"
                    v-model="item.tel"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                  />
                </VCol>
                <VCol cols="12" md="6" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="Email"
                    cols="12"
                    md="2"
                    >เมล(@kmutnb.ac.th เท่านั้น)
                  </label>
                  <AppTextField
                    id="email"
                    v-model="item.email"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                  />
                </VCol>

                <VDivider class="mt-4 mb-4" />

                <VCol cols="12" md="12" class="d-flex">
                  <VIcon size="22" icon="tabler-users" style="opacity: 1" />
                  <h4 class="pt-1 pl-3 pb-2">ผู้ที่ติดต่อได้</h4>
                </VCol>

                <VCol cols="12" md="6" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="contact1_name"
                    cols="12"
                    md="4"
                    >บุคคลที่ติตต่อได้ 1
                  </label>
                  <AppTextField
                    id="contact1_name"
                    v-model="item.contact1_name"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="contact1_relation"
                    cols="12"
                    md="4"
                    >ความสัมพันธ์
                  </label>
                  <AppTextField
                    id="contact1_relation"
                    v-model="item.contact1_relation"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="contact1_tel"
                    cols="12"
                    md="4"
                    >เบอร์โทรศัพท์
                  </label>
                  <AppTextField
                    id="contact1_tel"
                    v-model="item.contact1_tel"
                    :rules="[requiredValidator]"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="6" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="contact2_name"
                    cols="12"
                    md="4"
                    >บุคคลที่ติตต่อได้ 2
                  </label>
                  <AppTextField
                    id="contact2_name"
                    v-model="item.contact2_name"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="contact2_relation"
                    cols="12"
                    md="4"
                    >ความสัมพันธ์
                  </label>
                  <AppTextField
                    id="contact2_relation"
                    v-model="item.contact2_relation"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="contact2_tel"
                    cols="12"
                    md="4"
                    >เบอร์โทรศัพท์
                  </label>
                  <AppTextField
                    id="contact2_tel"
                    v-model="item.contact2_tel"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>
              </VRow>
              <!--  -->
            </VWindowItem>

            <!-- ข้อมูลสุขภาพ -->
            <VWindowItem>
              <VRow>
                <VCol cols="12" md="12" class="d-flex">
                  <VIcon size="22" icon="tabler-heart" style="opacity: 1" />
                  <h4 class="pt-1 pl-1">ข้อมูลสุขภาพ</h4>
                </VCol>
                <VCol style="margin-top: -1.5em">
                  <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
                </VCol>
              </VRow>

              <VRow>
                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="blood_group"
                    cols="12"
                    md="2"
                    >กลุ่มเลือด :
                  </label>
                  <AppSelect
                    v-model="item.blood_group"
                    :items="selectOptions.blood_groups"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    variant="outlined"
                    placeholder="Blood Group"
                    clearable
                  />
                </VCol>
                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="height"
                    cols="12"
                    md="4"
                    >ส่วนสูง(ซม.) :
                  </label>
                  <AppTextField
                    id="height"
                    v-model="item.height"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                    type="number"
                  />
                </VCol>
                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="weight"
                    cols="12"
                    md="4"
                    >น้ำหนัก(กก.) :
                  </label>
                  <AppTextField
                    id="weight"
                    v-model="item.weight"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                    type="number"
                  />
                </VCol>

                <VCol cols="12" md="3" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="emergency_tel"
                    cols="12"
                    md="4"
                    >เบอร์โทรฉุกเฉิน :
                  </label>
                  <AppTextField
                    id="emergency_tel"
                    v-model="item.emergency_tel"
                    :disabled="!disabled"
                    :rules="[requiredValidator]"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="12" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="congenital_disease"
                    cols="12"
                    md="2"
                    >โรคประจำตัว (กรณีไม่มีข้อมูลให้ทำการใช้เครื่องหมาย "-")
                  </label>
                  <AppTextarea
                    id="congenital_disease"
                    v-model="item.congenital_disease"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="12" class="align-items-center">
                  <label
                    class="v-label font-weight-bold"
                    for="drug_allergy"
                    cols="12"
                    md="2"
                    >ประวัติการแพ้ยา (กรณีไม่มีข้อมูลให้ทำการใช้เครื่องหมาย "-")
                  </label>
                  <AppTextarea
                    id="drug_allergy"
                    v-model="item.drug_allergy"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>
              </VRow>
            </VWindowItem>

            <!-- ข้อมูลเอกสาร -->
            <VWindowItem>
              <!-- ใบประกาศ -->
              <VRow>
                <VCol cols="12" md="12" class="d-flex">
                  <VIcon size="22" icon="tabler-books" style="opacity: 1" />
                  <h4 class="pt-1 pl-1">ใบประกาศนียบัตร</h4>
                </VCol>
                <!--
                  <VCol style="margin-top: -1.5em">
                  <small> หมายเหตุ : โปรดระบุข้อมูลให้ครบถ้วน </small>
                  </VCol> 
                -->
              </VRow>
              <VRow
                v-for="(certItem, index) in documents_certificate"
                :key="'cert' + index"
              >
                <VCol cols="12" md="5">
                  <label
                    class="v-label font-weight-bold"
                    for="weight"
                    cols="12"
                    md="5"
                    >ชื่อใบประกาศ :
                  </label>
                  <AppTextField
                    :id="'certificate_name_' + index"
                    v-model="certItem.document_name"
                    :disabled="!disabled"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="5">
                  <label
                    class="v-label font-weight-bold"
                    for="weight"
                    cols="12"
                    md="5"
                    >ไฟล์ใบประกาศ :
                  </label>
                  <VFileInput
                    :id="'cert-file-' + index"
                    v-model="certItem.document_file"
                    :disabled="!disabled"
                    label="Upload Certificate"
                    persistent-placeholder
                  />
                </VCol>

                <VCol cols="12" md="1" class="pl-2 align-self-end">
                  <VBtn
                    style="width: 100%"
                    :color="
                      certItem.document_file_old == null
                        ? 'secondary'
                        : 'primary'
                    "
                    :disabled="certItem.document_file_old == null"
                    :href="
                      certItem.document_file_old != null
                        ? certItem.document_file_old
                        : '/'
                    "
                    target="_blank"
                  >
                    View File
                  </VBtn>
                </VCol>

                <VCol cols="12" md="1" class="pl-2 align-self-end">
                  <VBtn
                    style="width: 100%"
                    color="error"
                    :disabled="!disabled"
                    @click="removeCertificateItem(index)"
                  >
                    Del
                  </VBtn>
                </VCol>
              </VRow>

              <VRow>
                <VCol cols="12" md="1" class="pl-2 align-self-end">
                  <VBtn
                    style="width: 100%"
                    color="info"
                    :disabled="!disabled"
                    @click="repeateAgain"
                  >
                    Add New
                  </VBtn>
                </VCol>
              </VRow>

              <VDivider class="mt-2 mb-4" />
              <!-- ใบอื่นๆ -->
              <VRow>
                <VCol cols="12" md="12" class="d-flex align-self-center">
                  <VIcon size="22" icon="tabler-books" style="opacity: 1" />
                  <h4 class="pt-1 pl-1">เอกสาร</h4>
                </VCol>
              </VRow>

              <VRow v-for="d in item.documents" :key="d.document_type_id">
                <VCol cols="12" md="2">
                  <label class="v-label font-weight-bold" for="document_name"
                    >{{ d.document_name }}
                  </label>
                </VCol>

                <VCol cols="12" md="8">
                  <VFileInput
                    v-model="d.document_file"
                    label="Upload File"
                    persistent-placeholder
                    :disabled="!disabled"
                  />
                </VCol>

                <VCol cols="12" md="2" class="pl-2 align-self-end">
                  <VBtn
                    style="width: 100%"
                    :color="
                      d.document_file_old == null ? 'secondary' : 'primary'
                    "
                    :disabled="d.document_file_old == null"
                    :href="
                      d.document_file_old != null ? d.document_file_old : '/'
                    "
                    target="_blank"
                  >
                    View File
                  </VBtn>
                </VCol>
              </VRow>

              <!--  -->
            </VWindowItem>
          </VWindow>

          <div class="d-flex justify-space-between mt-8">
            <VBtn
              color="secondary"
              variant="tonal"
              :disabled="currentStep === 0"
              @click="currentStep--"
            >
              <VIcon icon="tabler-chevron-left" start class="flip-in-rtl" />
              Previous
            </VBtn>

            <VBtn
              v-if="studentSteps.length - 1 === currentStep"
              color="success"
              append-icon="tabler-check"
              :disabled="!disabled"
              @click="onSubmit"
            >
              submit
            </VBtn>

            <VBtn v-else @click="currentStep++">
              Next
              <VIcon icon="tabler-chevron-right" end class="flip-in-rtl" />
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>

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

<style lang="scss">
.checkout-stepper {
  .stepper-icon-step {
    .step-wrapper + svg {
      margin-inline: 3.5rem !important;
    }
  }
}

.note {
  margin-block-start: -1.5em;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
