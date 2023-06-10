<script setup>
import dayjs from "dayjs";
import Swal from "sweetalert2";
import "sweetalert2/src/sweetalert2.scss";
import { useRoute, useRouter } from "vue-router";
// import locale from "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";

import { useCwieDataStore } from "./useCwieDataStore";

import { form_statuses, text_statuses } from "@/data-constant/data";

// const route = useRoute();
dayjs.extend(buddhistEra);
const route = useRoute();
const router = useRouter();
const cwieDataStore = useCwieDataStore();

const student = ref({});
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
const currentTab = ref(0);

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const selectOptions = ref({
  provinces: [],
  amphurs: [],
  tumbols: [],
  teachers: [],
  document_types: [],
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

const fetchTeachers = () => {
  cwieDataStore
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

const fetchDocumentTypes = () => {
  cwieDataStore
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
fetchDocumentTypes();

const fetchStudentDocuments = () => {
  cwieDataStore
    .fetchStudentDocuments({
      student_id: student.value.id,
    })
    .then((response) => {
      if (response.status === 200) {
        const { data } = response.data;

        let document = data.filter((d) => {
          return d.document_type_id != 1;
        });

        student.value.documents = student.value.documents.map((d) => {
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
  cwieDataStore
    .fetchStudents({
      // id: route.params.id,
      username: userData.username.slice(1, userData.username.length),
      includeAll: true,
      // get id self
    })
    .then((response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        student.value = { ...data[0] };

        student.value.faculty_name = student.value.faculty_name.replace(
          "คณะ",
          ""
        );

        student.value.major_name = student.value.major_name
          ? student.value.major_name.replace("สาขาวิชา", "")
          : "";

        student.value.status = 1;

        student.value.documents = selectOptions.value.document_types.map(
          (d) => {
            return {
              id: null,
              document_file: null,
              document_file_old: ref(null),
              document_type_id: d.value,
              document_name: d.title,
              student_id: student.value.id,
            };
          }
        );

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
      cwieDataStore
        .editStudent({
          ...student.value,
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
          student_id: student.value.id,
          document_type_id: d.document_type_id,
          document_name: d.document_name,
          document_file:
            d.document_file.length !== 0 ? d.document_file[0] : null,
        };

        if (d.id == null) {
          await cwieDataStore
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
          await cwieDataStore
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
          await cwieDataStore
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
          await cwieDataStore
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
  }).then(() => {
    isOverlay.value = false;
    router.push({ name: "basic-settings-user" });
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

        cwieDataStore
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
<style lang="scss">
.checkout-stepper {
  .stepper-icon-step {
    .step-wrapper + svg {
      margin-inline: 3.5rem !important;
    }
  }
}
</style>
<template>
  <div>
    <VRow>
      <VCol cols="12" md="3">
        <VCard title="">
          <VCardText>
            <span class="font-weight-bold">ชื่อ : </span>
            <span
              >{{ student.prefix_name }}{{ student.firstname }}
              {{ student.surname }}</span
            ></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">รหัสนักศึกษา : </span>
            <span>{{ student.student_code }}</span></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">คณะ : </span>
            <span>{{ student.faculty_name }}</span></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">สาขาวิชา : </span>
            <span>{{}}</span></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">ชั้นปี : </span>
            <span>{{ student.class_year }}</span></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">สถานะ : </span>
            <VChip label :color="form_statuses[student.status]">{{
              text_statuses[student.status]
            }}</VChip>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="9">
        <VCard class="pa-5">
          <VTabs v-model="currentTab">
            <VTab
              ><VIcon
                size="16"
                icon="tabler-user"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลทั่วไป</VTab
            >
            <VTab
              ><VIcon
                size="16"
                icon="tabler-heart"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลสุขภาพ</VTab
            >
            <VTab>
              <VIcon
                size="16"
                icon="tabler-books"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลเอกสาร</VTab
            >
          </VTabs>

          <VCardText>
            <VWindow v-model="currentTab">
              <VWindowItem v-for="item in 3" :key="item"> asdasd </VWindowItem>
            </VWindow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

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

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
