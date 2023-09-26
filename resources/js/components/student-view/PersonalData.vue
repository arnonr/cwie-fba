<script setup>
import { useStudentViewStore } from "./useStudentViewStore";
import { form_statuses, statusShow } from "@/data-constant/data";
import VuePdfEmbed from "vue-pdf-embed";
import { watch } from "vue";

const props = defineProps(["student_id", "status_id"]);

const studentViewStore = useStudentViewStore();

// var UI
const isOverlay = ref(false);
const currentTab = ref(0);

// var
const student = ref({});
const documents_certificate = ref([]);

const selectOptions = ref({
  document_types: [],
});

// Fetch
const fetchProvinces = () => {
  studentViewStore
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

const fetchDocumentTypes = () => {
  studentViewStore
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
  studentViewStore
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

const fetchStudent = () => {
  studentViewStore
    .fetchStudents({
      id: props.student_id,
      includeAll: true,
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

        if (
          student.value.id &&
          student.value.prefix_id &&
          student.value.firstname &&
          student.value.surname &&
          student.value.citizen_id &&
          student.value.address &&
          student.value.province_id &&
          student.value.amphur_id &&
          student.value.tumbol_id &&
          student.value.tel &&
          student.value.email &&
          student.value.faculty_id &&
          student.value.class_year &&
          student.value.class_room &&
          student.value.advisor_id &&
          student.value.gpa &&
          student.value.contact1_name &&
          student.value.contact1_relation &&
          student.value.contact1_tel &&
          student.value.blood_group &&
          student.value.emergency_tel &&
          student.value.height &&
          student.value.weight
        ) {
        }

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

watch(props, () => {
  if (props.student_id) {
    fetchDocumentTypes();
  }
});

onMounted(() => {
  window.scrollTo(0, 0);
  if (props.student_id) {
    fetchDocumentTypes();
  }
});
</script>
<style lang="scss">
.swal2-container {
  z-index: 20001 !important;
}
</style>
<template>
  <div>
    <VRow>
      <VCol cols="12" md="3">
        <VCard title="" class="pa-3">
          <VCardText>
            <VImg
              v-if="
                student.photo_file &&
                (student.photo_file.includes('jpg') ||
                  student.photo_file.includes('jpeg') ||
                  student.photo_file.includes('png'))
              "
              :src="student.photo_file"
              width="100"
              height="120"
              class="mx-auto"
            />
            <vue-pdf-embed
              class="mx-auto"
              :source="student.photo_file"
              v-if="student.photo_file && student.photo_file.includes('pdf')"
              style="width: 100px"
            />
          </VCardText>
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
            <span class="font-weight-bold">สาขาวิชา : </span>
            <span>{{ student.major_name }}</span></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">ชั้นปี : </span>
            <span>{{ student.class_year }}</span></VCardText
          >
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">สถานะ : </span>
            <VChip label :color="form_statuses[props.status_id]">{{
              statusShow(props.status_id)
            }}</VChip>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="9">
        <VCard class="pa-5">
          <VTabs v-model="currentTab">
            <VTab>
              <VIcon
                size="16"
                icon="tabler-user"
                style="opacity: 1"
                class="mr-1"
              />
              ข้อมูลทั่วไป
            </VTab>
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
              <VWindowItem>
                <VRow>
                  <VCol cols="12" md="12">
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-book"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ข้อมูลการศึกษา</span
                    >
                  </VCol>

                  <VCol cols="12" md="3">
                    <span>ห้อง : </span>
                    <span>{{ student.class_room }} </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>อาจารย์ที่ปรึกษา : </span>
                    <span>{{ student.advisor_name }} </span>
                  </VCol>
                  <VCol cols="12" md="3">
                    <span>เกรดเฉลี่ย : </span>
                    <span>{{ student.gpa }} </span>
                  </VCol>

                  <VDivider></VDivider>
                  <!-- address -->
                  <VCol cols="12" md="12">
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-map-pin"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ข้อมูลที่อยู่</span
                    >
                  </VCol>
                  <VCol cols="12" md="12">
                    <span>ที่อยู่ปัจจุบัน : </span>
                    <span>{{ student.address }} </span>
                  </VCol>

                  <VCol cols="12" md="4">
                    <span>จังหวัด : </span>
                    <span>{{ student.province_name }} </span>
                  </VCol>
                  <VCol cols="12" md="4">
                    <span>อำเภอ/เขต : </span>
                    <span>{{ student.amphur_name }} </span>
                  </VCol>
                  <VCol cols="12" md="4">
                    <span>ตำบล/แขวง : </span>
                    <span>{{ student.tumbol_name }} </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>เบอร์โทรศัพท์ : </span>
                    <span>{{ student.tel }} </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>เมล : </span>
                    <span>{{ student.email }} </span>
                  </VCol>
                  <VDivider></VDivider>
                  <VCol cols="12" md="12">
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-users"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ผู้ที่ติดต่อได้</span
                    >
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>บุคคลที่ติดต่อได้1 : </span>
                    <span>{{ student.contact1_name }} </span>
                  </VCol>
                  <VCol cols="12" md="3">
                    <span>ความสัมพันธ์ : </span>
                    <span>{{ student.contact1_relation }} </span>
                  </VCol>
                  <VCol cols="12" md="3">
                    <span>โทร : </span>
                    <span>{{ student.contact1_tel }} </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>บุคคลที่ติดต่อได้2 : </span>
                    <span>{{ student.contact2_name }} </span>
                  </VCol>
                  <VCol cols="12" md="3">
                    <span>ความสัมพันธ์ : </span>
                    <span>{{ student.contact2_relation }} </span>
                  </VCol>
                  <VCol cols="12" md="3">
                    <span>โทร : </span>
                    <span>{{ student.contact2_tel }} </span>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol cols="12" md="12">
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-heart"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ข้อมูลสุขภาพ</span
                    >
                  </VCol>

                  <VCol cols="12" md="4">
                    <span>กลุ่มเลือด : </span>
                    <span>{{ student.blood_group }} </span>
                  </VCol>
                  <VCol cols="12" md="4">
                    <span>ส่วนสูง (ซม.) : </span>
                    <span>{{ student.height }} </span>
                  </VCol>
                  <VCol cols="12" md="4">
                    <span>น้ำหนัก (กก.) : </span>
                    <span>{{ student.weight }} </span>
                  </VCol>
                  <VCol cols="12" md="12">
                    <span>เบอร์โทรฉุกเฉิน : </span>
                    <span>{{ student.emergency_tel }} </span>
                  </VCol>
                  <VCol cols="12" md="12">
                    <span>โรคประจำตัว : </span>
                    <span>{{ student.congenital_disease }} </span>
                  </VCol>
                  <VCol cols="12" md="12">
                    <span>ประวัติการแพ้ยา : </span>
                    <span>{{ student.drug_allergy }} </span>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol cols="12" md="12">
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-books"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ประกาศนียบัตร</span
                    >
                  </VCol>
                  <VCol
                    cols="12"
                    md="12"
                    v-for="(d, index) in documents_certificate"
                    :key="index"
                  >
                    <span class="font-weight-bold"
                      >{{ d.document_name }} :
                    </span>
                    <a
                      :href="
                        d.document_file_old != null ? d.document_file_old : '#'
                      "
                      target="_blank"
                      ><span>
                        <VIcon
                          size="16"
                          icon="tabler-file"
                          style="opacity: 1"
                          class="mr-1"
                        />เอกสาร</span
                      >
                    </a>
                  </VCol>
                  <VDivider></VDivider>
                  <VCol cols="12" md="12">
                    <span class="font-weight-bold">
                      <VIcon
                        size="16"
                        icon="tabler-books"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      เอกสาร</span
                    >
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    v-for="(d, index) in student.documents"
                    :key="index"
                  >
                    <span class="font-weight-bold"
                      >{{ d.document_name }} :
                    </span>
                    <a
                      :href="
                        d.document_file_old != null ? d.document_file_old : '#'
                      "
                      target="_blank"
                      ><span>
                        <VIcon
                          size="16"
                          icon="tabler-file"
                          style="opacity: 1"
                          class="mr-1"
                        />เอกสาร</span
                      >
                    </a>
                  </VCol>
                </VRow>
              </VWindowItem>
            </VWindow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
