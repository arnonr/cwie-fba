<script setup>
import VisitApprove from "@/components/visit/VisitApprove.vue";
import VisitDownload from "@/components/visit/VisitDownload.vue";
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import { useVisitViewStore } from "./useVisitViewStore";

import VueDatePicker from "@vuepic/vue-datepicker";

import "@vuepic/vue-datepicker/dist/main.css";

dayjs.extend(buddhistEra);
const visitViewStore = useVisitViewStore();
const route = useRoute();
const router = useRouter();

const props = defineProps(["user_type", "student_id"]);
const emit = defineEmits(["refresh-data", "close"]);
const teacherData = JSON.parse(localStorage.getItem("teacherData"));
let userData = JSON.parse(localStorage.getItem("userData"));

const isOverlay = ref(false);
const student = ref({});

const item = ref({
  supervision_id: teacherData ? teacherData.id : "",
  form_id: null,
  visit_date: null,
  visit_time: null,
  co_name: null,
  co_position: null,
  visit_type: null,
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
});

const items = ref([]);
const formActive = ref(null);

const visitRejectLog = ref([]);
const selectOptions = ref({
  provinces: [],
  amphurs: [],
});

const provinces = ref([]);
const amphurs = ref([]);
// Fetch
const fetchProvinces = () => {
  visitViewStore
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
  visitViewStore
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
  visitViewStore
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
  visitViewStore
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
  visitViewStore
    .fetchVisits({
      form_id: formActive.value.id,
      perPage: 10,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      includeAll: true,
    })
    .then(async (response) => {
      if (response.data.message == "success") {
        const { data } = response.data;
        if (data.length != 0) {
          item.value = data[0];

          items.value = data;
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
  visitViewStore
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

onMounted(() => {
  window.scrollTo(0, 0);
});

const format = (date) => {
  const day = dayjs(date).locale("th").format("DD");
  const month = dayjs(date).locale("th").format("MMM");
  const year = date.getFullYear() + 543;

  return `${day} ${month} ${year}`;
};

// Province
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

const refreshData = () => {
  emit("refresh-data");
  emit("close");
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

    <VCol cols="12" md="12">
      <!-- Action -->
      <VisitApprove
        v-if="
          props.user_type == 'chairman' ||
          props.user_type == 'major-head' ||
          props.user_type == 'staff'
        "
        :student_id="props.student_id"
        :formActive="formActive"
        :visitActive="item"
        :visitAll="items"
        :user_type="props.user_type"
        @refresh-data="refreshData"
      />

      <VisitDownload
        v-if="props.user_type == 'supervisor'"
        :student_id="props.student_id"
        :formActive="formActive"
        :visitAll="items"
        :visitActive="item"
        :student="student"
        :user_type="props.user_type"
        @refresh-data="refreshData"
      />
    </VCol>

    <VCard title="">
      <VCardItem>
        <VRow class="mb-1">
          <VCol cols="12" md="12" class="text-center">
            <h3 v-if="items.length > 1" class="text-red">
              ขอแก้ไขข้อมูลออกนิเทศ
            </h3>
          </VCol>

          <VCol cols="12" md="12">
            <VRow>
              <VCol cols="12" md="6">
                <span>ประเภทการออกนิเทศ : </span>
                <span>
                  {{ item.visit_type }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>วันที่ออกนิเทศ : </span>
                <span>
                  {{
                    item.visit_date
                      ? dayjs(item.visit_date)
                          .locale("th")
                          .format("DD MMM BBBB") +
                        " " +
                        item.visit_time.substring(
                          0,
                          item.visit_time.length - 3
                        ) +
                        " น."
                      : ""
                  }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>ชื่อ-สกุล พี่เลี้ยง : </span>
                <span>
                  {{ item.co_name }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>ตำแหน่ง พี่เลี้ยง : </span>
                <span>
                  {{ item.co_position }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>เบอร์โทรศัพท์ พี่เลี้ยง : </span>
                <span>
                  {{ item.co_phone }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span> เลขที่หนังสือขอเข้านิเทศ: </span>
                <span>
                  {{ item.document_number }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>วันที่หนังสือขอเข้านิเทศ: </span>
                <span>
                  {{
                    item.document_date
                      ? dayjs(item.document_date)
                          .locale("th")
                          .format("DD MMM BBBB")
                      : ""
                  }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>วันที่ประธานอาจารย์นิเทศอนุมัติ: </span>
                <span>
                  {{
                    item.major_head_approve_at
                      ? dayjs(item.major_head_approve_at)
                          .locale("th")
                          .format("DD MMM BBBB")
                      : ""
                  }}
                </span>
              </VCol>
              <VCol cols="12" md="6">
                <span>วันที่ประธานบริหารอนุมัติ: </span>
                <span>
                  {{
                    item.chairman_approved_at
                      ? dayjs(item.chairman_approved_at)
                          .locale("th")
                          .format("DD MMM BBBB")
                      : ""
                  }}
                </span>
              </VCol>
            </VRow>
          </VCol>

          <VDivider class="mt-4 mb-4"></VDivider>

          <VCol cols="12" md="12" class="d-flex">
            <VIcon size="22" icon="tabler-user" style="opacity: 1" />
            <h4 class="pt-1 pl-1">ข้อมูลรายงานผลการนิเทศ</h4>
          </VCol>
          <VCol cols="12" md="6">
            <span>วันที่ส่งรายงานผล: </span>
            <span>
              {{
                item.send_report_at
                  ? dayjs(item.send_report_at)
                      .locale("th")
                      .format("DD MMM BBBB")
                  : ""
              }}
            </span>
          </VCol>

          <VCol cols="12" md="6">
            <span>วันที่ยืนยันรายงานผล: </span>
            <span>
              {{
                item.confirm_report_at
                  ? dayjs(item.confirm_report_at)
                      .locale("th")
                      .format("DD MMM BBBB")
                  : ""
              }}
            </span>
          </VCol>

          <!-- ไฟล์ -->
          <VCol cols="12" md="6">
            <span>ไฟล์เอกสาร1 : </span>
            <a v-if="item.report_file" :href="item.report_file" target="_blank"
              ><span>
                <VIcon
                  size="16"
                  icon="tabler-file"
                  style="opacity: 1"
                  class="mr-1"
                />เอกสาร</span
              >
            </a>
            <span v-else>-</span>
          </VCol>

          <VCol cols="12" md="6">
            <span>ไฟล์เอกสาร2 : </span>
            <a
              v-if="item.report2_file"
              :href="item.report2_file"
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
            <span v-else>-</span>
          </VCol>

          <VCol cols="12" md="12">
            <span>หมายเหตุ: </span>
            <span>
              {{ item.visit_detail }}
            </span>
          </VCol>

          <VCol cols="12" md="12">
            <span>ต้องการรับนักศึกษาในรอบหน้า: </span>
            <span>
              {{ item.is_recruit == 1 ? "ต้องการ" : "ไม่ต้องการ" }}
            </span>
          </VCol>

          <VCol cols="12" md="12" class="text-error">
            <h3 class="text-error">Reject</h3>
            <span>รายละเอียด: </span>
            <span>
              {{ item.reject_report_comment }}
            </span>
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
        </VRow>
      </VCardItem>
    </VCard>

    <VRow class="mt-5">
      <VCol cols="12" md="12" v-for="(it, index) in items" :key="index">
        <v-expansion-panels v-if="it.active != 1">
          <v-expansion-panel
            :title="`ครั้งที่ ${items.length - index} (ยกเลิก)`"
          >
            <v-expansion-panel-text class="pa-5">
              <VRow>
                <VCol cols="12" md="12">
                  <VRow>
                    <VCol cols="12" md="6">
                      <span>ประเภทการออกนิเทศ : </span>
                      <span>
                        {{ it.visit_type }}
                      </span>
                    </VCol>
                    <VCol cols="12" md="6">
                      <span>วันที่ออกนิเทศ : </span>
                      <span>
                        {{
                          it.visit_date
                            ? dayjs(it.visit_date)
                                .locale("th")
                                .format("DD MMM BBBB") +
                              " " +
                              it.visit_time.substring(
                                0,
                                it.visit_time.length - 3
                              ) +
                              " น."
                            : ""
                        }}
                      </span>
                    </VCol>
                    <VCol cols="12" md="6">
                      <span>ชื่อ-สกุล พี่เลี้ยง : </span>
                      <span>
                        {{ it.co_name }}
                      </span>
                    </VCol>
                    <VCol cols="12" md="6">
                      <span>ตำแหน่ง พี่เลี้ยง : </span>
                      <span>
                        {{ it.co_position }}
                      </span>
                    </VCol>
                    <VCol cols="12" md="6">
                      <span>เบอร์โทรศัพท์ พี่เลี้ยง : </span>
                      <span>
                        {{ it.co_phone }}
                      </span>
                    </VCol>
                    <VCol cols="12" md="6">
                      <span class="text-error">วันที่ยกเลิก : </span>
                      <span class="text-error">
                        {{
                          it.cancel_at
                            ? dayjs(it.cancel_at)
                                .locale("th")
                                .format("DD MMM BBBB")
                            : ""
                        }}
                      </span>
                    </VCol>
                    <VCol cols="12" md="6">
                      <span class="text-error">ไฟล์การยกเลิก : </span>
                      <span>
                        <!-- {{ it.cancel_file ? : it.it.cancel_file }} -->
                      </span>
                      <a
                        class="text-error"
                        v-if="it.cancel_file != null"
                        :href="it.cancel_file"
                        target="_blank"
                        ><span>
                          <VIcon
                            size="16"
                            icon="tabler-file"
                            style="opacity: 1"
                            class="mr-1" /></span
                      ></a>
                    </VCol>
                    <VCol cols="12" md="12">
                      <span class="text-error">เหตุผลการยกเลิก : </span>
                      <span class="text-error">
                        {{ it.cancel_description }}
                      </span>
                    </VCol>
                  </VRow>
                </VCol>
              </VRow>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </VCol>
    </VRow>

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
