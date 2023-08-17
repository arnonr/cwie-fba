<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { useRoute, useRouter } from "vue-router";
import { useCwieDataStore } from "../useCwieDataStore";
import { form_statuses, statusShow, visit_status } from "@/data-constant/data";

import { PDFDocument, rgb } from "pdf-lib";
import fontkit from "@pdf-lib/fontkit";
import "vue3-pdf-app/dist/icons/main.css";

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

const visitActive = ref({
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

const visit = ref([]);
const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();
const isDialogConfirmVisible = ref(false);

const chairman = ref({
  prefix: "",
  firstname: "",
  surname: "",
  signature_file: "",
  executive_position: "",
});

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
        fetchTumbols();
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
  console.log("FREEDOM");
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

        for (let i = 0; i < data.length; i++) {
          await cwieDataStore
            .fetchTeachers({ id: data[i].chairman_id })
            .then((response) => {
              if (response.status === 200) {
                chairman.value = response.data.data[0];
                console.log(chairman.value);
              } else {
                console.log("error");
              }
            })
            .catch((error) => {
              console.error(error);
              isOverlay.value = false;
            });

          await cwieDataStore
            .fetchMajorHeads({
              semester_id: data[i].semester_id,
              major_id: item.value.major_id,
              active: 1,
              includeAll: true,
            })
            .then((res) => {
              data[i].major_head_name = res.data.data[0].teacher_name;
            });
        }

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
      perPage: 20,
      currentPage: 1,
      orderBy: "active",
      order: "desc",
      includeAll: true,
    })
    .then(async (response) => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data;

        visitActive.value = data[0];

        visit.value = data;
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
    console.log(value);
  }
);

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

const generatePDF1 = async () => {
  isOverlay.value = true;
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );
  let url = "";
  url = window.location.origin + "/storage/pdf/book3.pdf";

  const existingPdfBytes = await fetch(url).then((res) => res.arrayBuffer());
  const pdfTemplate = await PDFDocument.load(existingPdfBytes);
  // Create PDF
  const pdfDoc = await PDFDocument.create();
  pdfDoc.registerFontkit(fontkit);
  const sarabunFont = await pdfDoc.embedFont(fontBytes);
  const sarabunBoldFont = await pdfDoc.embedFont(fontBytesBold);

  const [existingPage] = await pdfDoc.copyPages(pdfTemplate, [0]);
  pdfDoc.addPage(existingPage);

  const defaultSize = {
    size: 16,
    font: sarabunFont,
    color: rgb(0, 0, 0),
  };

  existingPage.drawText(formItem.value.term.toString(), {
    x: 355, //คอลัมน์ ซ้ายไปขวา
    y: 734, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.semester_year.toString(), {
    x: 377, //คอลัมน์ ซ้ายไปขวา
    y: 734, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.supervision_name, {
    x: 190, //คอลัมน์ ซ้ายไปขวา
    y: 710, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.semester_year.toString(), {
    x: 500, //คอลัมน์ ซ้ายไปขวา
    y: 690, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.company_name, {
    x: 170, //คอลัมน์ ซ้ายไปขวา
    y: 647, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    visitActive.value.address != null ? visitActive.value.address : "",
    {
      x: 100,
      y: 622,
      ...defaultSize,
    }
  );

  console.log(visitActive.value.tumbol_id);
  console.log(selectOptions.value.tumbols);
  existingPage.drawText(responseTumbolName(visitActive.value.tumbol_id), {
    x: 115,
    y: 597,
    ...defaultSize,
  });

  existingPage.drawText(responseAmphurName(visitActive.value.amphur_id), {
    x: 290,
    y: 597,
    ...defaultSize,
  });

  existingPage.drawText(responseProvinceName(visitActive.value.province_id), {
    x: 435,
    y: 597,
    ...defaultSize,
  });

  existingPage.drawText(visitActive.value.co_name, {
    x: 130,
    y: 573,
    ...defaultSize,
  });

  existingPage.drawText(visitActive.value.co_position, {
    x: 320,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(visitActive.value.co_phone, {
    x: 475,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(
    item.value.prefix_name + item.value.firstname + " " + item.value.surname,
    {
      x: 135,
      y: 550,
      ...defaultSize,
    }
  );

  existingPage.drawText(item.value.student_code, {
    x: 370,
    y: 550,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(visitActive.value.visit_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 72,
      y: 525,
      ...defaultSize,
    }
  );
  console.log(visitActive.value.visit_time);
  existingPage.drawText(visitActive.value.visit_time.toString(), {
    x: 180,
    y: 525,
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.supervision_name, {
    x: 420, //คอลัมน์ ซ้ายไปขวา
    y: 338, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(visitActive.value.created_at).locale("th").format("DD MMMM BBBB"),
    {
      x: 420, //คอลัมน์ ซ้ายไปขวา
      y: 296, //แถว ยิ่งมากยิ่งอยู่ด้านบน
      ...defaultSize,
    }
  );

  existingPage.drawText(formItem.value.major_head_name, {
    x: 110, //คอลัมน์ ซ้ายไปขวา
    y: 267, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(visitActive.value.created_at).locale("th").format("DD MMMM BBBB"),
    {
      x: 110, //คอลัมน์ ซ้ายไปขวา
      y: 247, //แถว ยิ่งมากยิ่งอยู่ด้านบน
      ...defaultSize,
    }
  );

  //   const sigUrl = chairman.value.signature_file;
  //   const sigImageBytes = await fetch(sigUrl).then((res) => res.arrayBuffer());

  //   let sigImage;
  //   try {
  //     sigImage = await pdfDoc.embedPng(sigImageBytes);
  //   } catch (error) {
  //     sigImage = await pdfDoc.embedJpg(sigImageBytes);
  //   }

  //   existingPage.drawImage(sigImage, {
  //     x: 110,
  //     y: 115,
  //     width: 100,
  //     height: 50,
  //   });

  existingPage.drawText(
    chairman.value.prefix +
      " " +
      chairman.value.firstname +
      " " +
      chairman.value.surname,
    {
      x: 110,
      y: 115,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(visitActive.value.created_at).locale("th").format("DD MMMM BBBB"),
    {
      x: 110, //คอลัมน์ ซ้ายไปขวา
      y: 94, //แถว ยิ่งมากยิ่งอยู่ด้านบน
      ...defaultSize,
    }
  );

  //   const sigUrl = chairman.value.signature_file;
  //   const sigImageBytes = await fetch(sigUrl).then((res) => res.arrayBuffer());

  //   let sigImage;
  //   try {
  //     sigImage = await pdfDoc.embedPng(sigImageBytes);
  //   } catch (error) {
  //     sigImage = await pdfDoc.embedJpg(sigImageBytes);
  //   }

  //   existingPage.drawImage(sigImage, {
  //     x: 310,
  //     y: 120,
  //     width: 100,
  //     height: 50,
  //   });

  //   existingPage.drawText(
  //     chairman.value.prefix +
  //       " " +
  //       chairman.value.firstname +
  //       " " +
  //       chairman.value.surname,
  //     {
  //       x: 300,
  //       y: 117,
  //       ...defaultSize,
  //     }
  //   );

  //   existingPage.drawText(chairman.value.executive_position, {
  //     x: 275,
  //     y: 96,
  //     ...defaultSize,
  //   });

  //   const [existingPage2] = await pdfDoc.copyPages(pdfTemplate, [1]);
  //   pdfDoc.addPage(existingPage2);

  const pdfBytes = await pdfDoc.save();
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */)
  );

  const link = document.createElement("a");
  link.href = objectPdf;
  link.download = "book.pdf";
  link.click();

  isOverlay.value = false;
};

const generatePDF2 = async () => {
  isOverlay.value = true;
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );
  let url = "";
  url = window.location.origin + "/storage/pdf/book4.pdf";

  const existingPdfBytes = await fetch(url).then((res) => res.arrayBuffer());
  const pdfTemplate = await PDFDocument.load(existingPdfBytes);
  // Create PDF
  const pdfDoc = await PDFDocument.create();
  pdfDoc.registerFontkit(fontkit);
  const sarabunFont = await pdfDoc.embedFont(fontBytes);
  const sarabunBoldFont = await pdfDoc.embedFont(fontBytesBold);

  const [existingPage] = await pdfDoc.copyPages(pdfTemplate, [0]);
  pdfDoc.addPage(existingPage);

  const defaultSize = {
    size: 16,
    font: sarabunFont,
    color: rgb(0, 0, 0),
  };

  existingPage.drawText(visitActive.value.document_number, {
    x: 80, //คอลัมน์ ซ้ายไปขวา
    y: 728, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(visitActive.value.document_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 335,
      y: 624,
      ...defaultSize,
    }
  );

  let co_name = "";
  if (visitActive.value.co_name == "-") {
    co_name = visitActive.value.co_position;
  } else {
    co_name =
      visitActive.value.co_name + " (" + visitActive.value.co_position + ")";
  }

  existingPage.drawText(co_name, {
    x: 105,
    y: 559,
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.company_name, {
    x: 205, //คอลัมน์ ซ้ายไปขวา
    y: 528, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(item.value.major_name, {
    x: 208, //คอลัมน์ ซ้ายไปขวา
    y: 506, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.term.toString(), {
    x: 75,
    y: 465,
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.semester_year, {
    x: 95,
    y: 465,
    ...defaultSize,
  });

  existingPage.drawText("1", {
    x: 165,
    y: 465,
    ...defaultSize,
  });

  existingPage.drawText(item.value.prefix_name + item.value.firstname, {
    x: 120,
    y: 432,
    ...defaultSize,
  });

  existingPage.drawText(item.value.surname, {
    x: 260,
    y: 432,
    ...defaultSize,
  });

  existingPage.drawText(item.value.class_year.toString(), {
    x: 391,
    y: 432,
    ...defaultSize,
  });

  existingPage.drawText(item.value.student_code, {
    x: 460,
    y: 432,
    ...defaultSize,
  });

  //
  existingPage.drawText(
    dayjs(visitActive.value.visit_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 355,
      y: 357,
      ...defaultSize,
    }
  );

  existingPage.drawText(visitActive.value.visit_time, {
    x: 477,
    y: 357,
    ...defaultSize,
  });

  existingPage.drawText(formItem.value.supervision_name, {
    x: 180,
    y: 303,
    ...defaultSize,
  });

  //

  const sigUrl = chairman.value.signature_file;
  const sigImageBytes = await fetch(sigUrl).then((res) => res.arrayBuffer());

  let sigImage;
  try {
    sigImage = await pdfDoc.embedPng(sigImageBytes);
  } catch (error) {
    sigImage = await pdfDoc.embedJpg(sigImageBytes);
  }

  existingPage.drawImage(sigImage, {
    x: 310,
    y: 173,
    width: 100,
    height: 50,
  });

  existingPage.drawText(
    chairman.value.prefix +
      " " +
      chairman.value.firstname +
      " " +
      chairman.value.surname,
    {
      x: 310,
      y: 168,
      ...defaultSize,
    }
  );

  existingPage.drawText(chairman.value.executive_position, {
    x: 285,
    y: 150,
    ...defaultSize,
  });

  const [existingPage2] = await pdfDoc.copyPages(pdfTemplate, [1]);
  pdfDoc.addPage(existingPage2);

  const pdfBytes = await pdfDoc.save();
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */)
  );

  const link = document.createElement("a");
  link.href = objectPdf;
  link.download = "book.pdf";
  link.click();

  isOverlay.value = false;
};
</script>
<style lang="scss">
.dp__input {
  color: #787878;
}
</style>
<template>
  <div>
    <div class="mb-2 text-right">
      <VBtn color="success" v-if="visitActive.visit_status == 4">
        รายงานผล</VBtn
      >
      <VBtn
        @click="generatePDF1"
        class="ml-2"
        color="primary"
        v-if="visitActive.document_number != null"
      >
        แบบฟอร์มขอออกนิเทศ</VBtn
      >
      <VBtn
        @click="generatePDF2"
        color="primary"
        class="ml-2"
        v-if="visitActive.document_number != null"
      >
        หนังสือขอเข้านิเทศ</VBtn
      >
    </div>
    <VCard title="ข้อมูลขอออกนิเทศ (ปัจจุบัน)">
      <VCardItem>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onValidate()"
        >
          <VRow class="mb-1">
            <VCol cols="12" md="12" class="d-flex">
              <VIcon size="22" icon="tabler-user" style="opacity: 1" />
              <h4 class="pt-1 pl-1">ข้อมูลการออกนิเทศ</h4>
            </VCol>

            <VCol cols="12" md="12" v-if="formItem != null">
              <VRow>
                <VCol cols="12" md="12">
                  <span>ประเภทการออกนิเทศ: </span>
                  <span>
                    {{ visitActive.visit_type }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>วันที่ออกนิเทศ : </span>
                  <span>
                    {{
                      dayjs(visitActive.visit_date)
                        .locale("th")
                        .format("DD MMMM BBBB")
                    }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>เวลาออกนิเทศ : </span>
                  <span>
                    {{ visitActive.visit_time }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>ชื่อ-นามสกุลพี่เลี้ยง : </span>
                  <span>
                    {{ visitActive.co_name }}
                  </span>
                </VCol>
                <VCol cols="12" md="6">
                  <span>ตำแหน่ง : </span>
                  <span>
                    {{ visitActive.co_position }}
                  </span>
                </VCol>

                <VCol cols="12" md="6">
                  <span>สถานะใบขอออกนิเทศ : </span>
                  <span v-if="visitActive.visit_status">
                    <!-- {{ visitActive.visit_status }} -->
                    {{ visit_status[visitActive.visit_status].title }}
                  </span>
                </VCol>
              </VRow>
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
                  <span>ตำบล : </span>
                  <span>
                    {{ responseTumbolName(formItem.workplace_tumbol_id) }}
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
          </VRow>
        </VForm>
      </VCardItem>
    </VCard>
    <div v-for="(vs, index) in visit" :key="index">
      <VCard
        v-if="vs.visit_id != visitActive.visit_id"
        title="ข้อมูลขอออกนิเทศ (ยกเลิก)"
        class="mt-10"
      >
        <VCardItem>
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onValidate()"
          >
            <VRow class="mb-1">
              <VCol cols="12" md="12" class="d-flex">
                <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                <h4 class="pt-1 pl-1">ข้อมูลการออกนิเทศ</h4>
              </VCol>

              <VCol cols="12" md="12">
                <VRow>
                  <VCol cols="12" md="12">
                    <span>ประเภทการออกนิเทศ: </span>
                    <span>
                      {{ vs.visit_type }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>วันที่ออกนิเทศ : </span>
                    <span>
                      {{
                        dayjs(vs.visit_date).locale("th").format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>เวลาออกนิเทศ : </span>
                    <span>
                      {{ vs.visit_time }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ชื่อ-นามสกุลพี่เลี้ยง : </span>
                    <span>
                      {{ vs.co_name }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>ตำแหน่ง : </span>
                    <span>
                      {{ vs.co_position }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>เหตุผลยกเลิก : </span>
                    <span>{{ vs.cancel_description }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>เอกสารการยกเลิก : </span>
                    <span>
                      <a :href="vs.cancel_file" target="_blank">
                        <span>
                          <VIcon
                            size="16"
                            icon="tabler-file"
                            style="opacity: 1"
                            class="mr-1"
                        /></span>
                      </a>
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>วันที่ยกเลิก : </span>
                    <span>{{
                      vs.cancel_at
                        ? dayjs(vs.cancel_at)
                            .locale("th")
                            .format("DD MMMM BBBB")
                        : ""
                    }}</span>
                  </VCol>
                </VRow>
              </VCol>
              <VDivider class="mt-4 mb-4"></VDivider>
            </VRow>
          </VForm>
        </VCardItem>
      </VCard>
    </div>

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
