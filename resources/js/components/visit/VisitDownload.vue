<script setup>
import { useVisitDownloadStore } from "./useVisitDownloadStore";
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { onMounted } from "vue";
import { PDFDocument, rgb } from "pdf-lib";
import fontkit from "@pdf-lib/fontkit";
import "vue3-pdf-app/dist/icons/main.css";

const visitDownloadStore = useVisitDownloadStore();
const props = defineProps([
  "user_type",
  "student_id",
  "student",
  "formActive",
  "visitActive",
  "visitAll",
]);
const emit = defineEmits(["refresh-data"]);

const isDialogVisible = ref(false);
const isDialogRejectVisible = ref(false);
const isOverlay = ref(false);
const isFormValid = ref(false);

let userData = JSON.parse(localStorage.getItem("userData"));

const new_visit_status = ref(null);
const date = ref({});
const rejectLog = ref({
  comment: "",
  reject_status_id: "",
  visit_id: "",
  user_id: userData.user_id,
});

const selectOptions = ref({
  recruits: [
    { title: "ต้องการ", value: 1 },
    { title: "ไม่ต้องการ", value: 0 },
  ],
});

const visit_report = ref({
  is_recruit: null,
  report_file: [],
  report2_file: [],
  report_status_id: null,
  send_report_at: null,
  visit_detail: "",
});

const chairman = ref({
  prefix: "",
  firstname: "",
  surname: "",
  signature_file: "",
  executive_position: "",
});

const province_name = ref("");
const amphur_name = ref("");
const tumbol_name = ref("");
const major_head_name = ref("");

watch(props, () => {
  if (props.visitActive != null) {
    fetchProvinces();
    fetchAmphurs();
    fetchTumbols();
    // if (props.user_type == "major-head") {
    //   rejectLog.value.reject_status_id = 1;
    //   new_visit_status.value = 2;
    //   date.value.major_head_approve_at = dayjs().format("YYYY-MM-DD");
    // } else if (props.user_type == "chairman") {
    //   rejectLog.value.reject_status_id = 2;
    //   new_visit_status.value = 3;
    //   date.value.chairman_approved_at = dayjs().format("YYYY-MM-DD");
    // } else if (props.user_type == "staff") {
    //   // Staff
    //   if (props.formActive.status_id == 4) {
    //     date.value.faculty_confirmed_at = dayjs().format("YYYY-MM-DD");
    //     rejectLog.value.reject_status_id = 3;
    //     new_visit_status.value = 5;
    //   } else if (props.formActive.status_id == 7) {
    //     date.value.confirm_response_at = dayjs().format("YYYY-MM-DD");
    //     rejectLog.value.reject_status_id = 4;
    //     new_visit_status.value = props.formActive.response_result;
    //   } else {
    //   }
    // } else {
    // }
  }
  if (props.formActive != null) {
    fetchTeachers();
  }
});

// Function
onMounted(() => {});
const fetchTeachers = async () => {
  await visitDownloadStore
    .fetchTeachers({ id: props.formActive.chairman_id })
    .then((response) => {
      if (response.status === 200) {
        chairman.value = response.data.data[0];
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });

  await visitDownloadStore
    .fetchMajorHeads({
      semester_id: props.formActive.semester_id,
      major_id: props.student.major_id,
      active: 1,
      includeAll: true,
    })
    .then((res) => {
      major_head_name.value = res.data.data[0].teacher_name;
    });
};

const fetchProvinces = () => {
  visitDownloadStore
    .fetchProvinces({
      province_id: props.visitActive.province_id,
    })
    .then((response) => {
      if (response.status === 200) {
        province_name.value = response.data.data[0].name_th;
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
  visitDownloadStore
    .fetchAmphurs({
      amphur_id: props.visitActive.amphur_id,
    })
    .then((response) => {
      if (response.status === 200) {
        amphur_name.value = response.data.data[0].name_th;
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
  visitDownloadStore
    .fetchTumbols({
      tumbol_id: props.visitActive.tumbol_id,
    })
    .then((response) => {
      if (response.status === 200) {
        tumbol_name.value = response.data.data[0].name_th;
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

const onRejectSubmit = () => {
  if (rejectLog.comment != "") {
    visitApproveStore
      .addRejectLog({
        ...rejectLog.value,
        active: 1,
      })
      .then((response) => {
        if (response.data.message == "success") {
          visitApproveStore
            .editVisit({
              visit_id: rejectLog.value.visit_id,
              visit_reject_status_id: rejectLog.value.reject_status_id,
              active: 1,
            })
            .then((response) => {
              if (response.data.message == "success") {
                isDialogRejectVisible.value = false;
                emit("refresh-data");
              }
            });

          localStorage.setItem("Rejected", 1);
          nextTick(() => {});
        } else {
          isOverlay.value = false;
          console.log("error");
        }
      })
      .catch((error) => {
        console.error(error);
      });
  } else {
    snackbarText.value = "โปรดระบุเหตุผล";
    snackbarColor.value = "error";
    isSnackbarVisible.value = true;
    isOverlay.value = false;
  }
};

const onSubmit = () => {
  // ส่งรายงานผล
  //   visit_report
  visitDownloadStore
    .sendReport({
      ...visit_report.value,
      visit_id: props.visitActive.visit_id,
      visit_status: 5,
      report_status_id: 1,
      send_report_at: dayjs().format("YYYY-MM-DD"),
      is_recruit: visit_report.value.is_recruit,
      report_file:
        visit_report.value.report_file.length !== 0
          ? visit_report.value.report_file[0]
          : null,
      report2_file:
        visit_report.value.report2_file.length !== 0
          ? visit_report.value.report2_file[0]
          : null,
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1);
        nextTick(() => {
          isDialogVisible.value = false;
          emit("refresh-data");
        });
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

const generatePDF = async () => {
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

  existingPage.drawText(props.formActive.term.toString(), {
    x: 355, //คอลัมน์ ซ้ายไปขวา
    y: 734, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year.toString(), {
    x: 377, //คอลัมน์ ซ้ายไปขวา
    y: 734, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.supervision_name, {
    x: 190, //คอลัมน์ ซ้ายไปขวา
    y: 710, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year.toString(), {
    x: 500, //คอลัมน์ ซ้ายไปขวา
    y: 690, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.company_name, {
    x: 170, //คอลัมน์ ซ้ายไปขวา
    y: 647, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    props.visitActive.address != null ? props.visitActive.address : "",
    {
      x: 100,
      y: 622,
      ...defaultSize,
    }
  );

  existingPage.drawText(tumbol_name.value, {
    x: 115,
    y: 597,
    ...defaultSize,
  });

  existingPage.drawText(amphur_name.value, {
    x: 290,
    y: 597,
    ...defaultSize,
  });

  existingPage.drawText(province_name.value, {
    x: 435,
    y: 597,
    ...defaultSize,
  });

  existingPage.drawText(props.visitActive.co_name, {
    x: 130,
    y: 573,
    ...defaultSize,
  });

  existingPage.drawText(props.visitActive.co_position, {
    x: 320,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(props.visitActive.co_phone, {
    x: 475,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(
    props.student.prefix_name +
      props.student.firstname +
      " " +
      props.student.surname,
    {
      x: 135,
      y: 550,
      ...defaultSize,
    }
  );

  existingPage.drawText(props.student.student_code, {
    x: 370,
    y: 550,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.visitActive.visit_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 72,
      y: 525,
      ...defaultSize,
    }
  );

  let visit_time = "";
  if (props.visitActive.visit_time) {
    let visit_time_arr = props.visitActive.visit_time.split(":");
    visit_time = visit_time_arr[0] + ":" + visit_time_arr[1];
  }
  existingPage.drawText(visit_time, { x: 187, y: 525, ...defaultSize });

  existingPage.drawText(props.formActive.supervision_name, {
    x: 420, //คอลัมน์ ซ้ายไปขวา
    y: 338, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.visitActive.created_at).locale("th").format("DD MMMM BBBB"),
    {
      x: 420, //คอลัมน์ ซ้ายไปขวา
      y: 296, //แถว ยิ่งมากยิ่งอยู่ด้านบน
      ...defaultSize,
    }
  );

  existingPage.drawText(major_head_name.value, {
    x: 110, //คอลัมน์ ซ้ายไปขวา
    y: 267, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.visitActive.created_at).locale("th").format("DD MMMM BBBB"),
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
    dayjs(props.visitActive.created_at).locale("th").format("DD MMMM BBBB"),
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

const generatePDF1 = async () => {
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );
  let url = "";
  url = window.location.origin + "/storage/pdf/visit_book.pdf";

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

  existingPage.drawText(props.visitActive.document_number, {
    x: 80, //คอลัมน์ ซ้ายไปขวา
    y: 728, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.visitActive.document_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 335,
      y: 624,
      ...defaultSize,
    }
  );

  let co_name = "";
  if (props.visitActive.co_name == "-") {
    co_name = props.visitActive.co_position;
  } else {
    co_name =
      props.visitActive.co_name + " (" + props.visitActive.co_position + ")";
  }

  existingPage.drawText(co_name, {
    x: 105,
    y: 559,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.company_name, {
    x: 205, //คอลัมน์ ซ้ายไปขวา
    y: 528, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.student.major_name, {
    x: 280, //คอลัมน์ ซ้ายไปขวา
    y: 506, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });
  existingPage.drawText(props.formActive.term.toString(), {
    x: 75,
    y: 465,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year, {
    x: 95,
    y: 465,
    ...defaultSize,
  });

  existingPage.drawText("1", {
    x: 165,
    y: 465,
    ...defaultSize,
  });

  existingPage.drawText(props.student.prefix_name + props.student.firstname, {
    x: 120,
    y: 432,
    ...defaultSize,
  });

  existingPage.drawText(props.student.surname, {
    x: 260,
    y: 432,
    ...defaultSize,
  });

  existingPage.drawText(props.student.class_year.toString(), {
    x: 380,
    y: 432,
    ...defaultSize,
  });

  existingPage.drawText(props.student.student_code, {
    x: 460,
    y: 432,
    ...defaultSize,
  });

  //
  existingPage.drawText(
    dayjs(props.visitActive.visit_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 355,
      y: 357,
      ...defaultSize,
    }
  );

  let visit_time = "";
  if (props.visitActive.visit_time) {
    let visit_time_arr = props.visitActive.visit_time.split(":");
    visit_time = visit_time_arr[0] + ":" + visit_time_arr[1];
  }
  existingPage.drawText(visit_time, {
    x: 485,
    y: 357,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.supervision_name, {
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

  // existingPage.drawText(chairman.value.executive_position, {
  //   x: 275,
  //   y: 135,
  //   ...defaultSize,
  // });

  const [existingPage2] = await pdfDoc.copyPages(pdfTemplate, [1]);
  pdfDoc.addPage(existingPage2);

  const pdfBytes = await pdfDoc.save();
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */)
  );

  const link = document.createElement("a");
  link.href = objectPdf;
  link.download = "visit_book_" + dayjs().format("YYYY_MM_DD") + ".pdf";
  link.click();

  isOverlay.value = false;
};

const generatePDF2 = async () => {
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );
  let url = "";
  url = window.location.origin + "/storage/pdf/change_visit_book.pdf";

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

  existingPage.drawText(props.formActive.supervision_name, {
    x: 180, //คอลัมน์ ซ้ายไปขวา
    y: 622, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.term.toString(), {
    x: 385,
    y: 598,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year, {
    x: 410,
    y: 598,
    ...defaultSize,
  });

  existingPage.drawText(
    props.student.prefix_name +
      props.student.firstname +
      " " +
      props.student.surname,
    {
      x: 130,
      y: 550,
      ...defaultSize,
    }
  );

  existingPage.drawText(props.student.student_code, {
    x: 405,
    y: 550,
    ...defaultSize,
  });

  existingPage.drawText(props.student.class_year.toString(), {
    x: 530,
    y: 550,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.company_name, {
    x: 250, //คอลัมน์ ซ้ายไปขวา
    y: 526, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  // วันเก่า
  existingPage.drawText(
    dayjs(props.visitAll[1].visit_date).locale("th").format("DD"),
    {
      x: 240,
      y: 502,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.visitAll[1].visit_date).locale("th").format("MMMM"),
    {
      x: 300,
      y: 502,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.visitAll[1].visit_date).locale("th").format("BBBB"),
    {
      x: 395,
      y: 502,
      ...defaultSize,
    }
  );

  let visit_time_old = "";
  if (props.visitAll[1].visit_time) {
    let visit_time_arr_old = props.visitAll[1].visit_time.split(":");
    visit_time_old = visit_time_arr_old[0] + ":" + visit_time_arr_old[1];
  }
  existingPage.drawText(visit_time_old, {
    x: 455,
    y: 502,
    ...defaultSize,
  });
  //

  // วันใหม่
  existingPage.drawText(
    dayjs(props.visitActive.visit_date).locale("th").format("DD"),
    {
      x: 100,
      y: 478,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.visitActive.visit_date).locale("th").format("MMMM"),
    {
      x: 160,
      y: 478,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.visitActive.visit_date).locale("th").format("BBBB"),
    {
      x: 260,
      y: 478,
      ...defaultSize,
    }
  );

  let visit_time = "";
  if (props.visitActive.visit_time) {
    let visit_time_arr = props.visitActive.visit_time.split(":");
    visit_time = visit_time_arr[0] + ":" + visit_time_arr[1];
  }
  existingPage.drawText(visit_time, {
    x: 325,
    y: 478,
    ...defaultSize,
  });
  //

  existingPage.drawText(props.formActive.supervision_name, {
    x: 410,
    y: 427,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.visitActive.created_at).locale("th").format("DD MMMM BBBB"),
    {
      x: 410,
      y: 380,
      ...defaultSize,
    }
  );

  //

  existingPage.drawText(major_head_name.value, {
    x: 110,
    y: 248,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.visitActive.major_head_approve_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 110,
      y: 228,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    chairman.value.prefix +
      " " +
      chairman.value.firstname +
      " " +
      chairman.value.surname,
    {
      x: 110,
      y: 94,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.visitActive.chairman_approved_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 110,
      y: 73,
      ...defaultSize,
    }
  );

  const pdfBytes = await pdfDoc.save();
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */)
  );

  const link = document.createElement("a");
  link.href = objectPdf;
  link.download = "visit_book_" + dayjs().format("YYYY_MM_DD") + ".pdf";
  link.click();

  isOverlay.value = false;
};
</script>

<template>
  <div>
    <div v-if="props.user_type == 'supervisor'">
      <VCol
        cols="12"
        md="12"
        class="text-right"
        v-if="props.visitActive != null"
      >
        <VBtn
          color="success"
          :disabled="props.visitActive.visit_status < 4"
          @click="
            () => {
              generatePDF();
            }
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          เอกสารขอออกนิเทศ
        </VBtn>

        <VBtn
          class="ml-2"
          color="success"
          :disabled="props.visitActive.visit_status < 4"
          @click="
            () => {
              generatePDF1();
            }
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          หนังสือขอเข้านิเทศ
        </VBtn>

        <VBtn
          class="ml-2"
          color="warning"
          v-if="props.visitActive.visit_status > 3 && props.visitAll.length > 1"
          @click="
            () => {
              generatePDF2();
            }
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          เอกสารขอเปลี่ยนแปลงการออกนิเทศ
        </VBtn>

        <VBtn
          class="ml-2"
          color="info"
          :disabled="props.visitActive.visit_status != 4"
          @click="
            () => {
              isDialogVisible = true;
            }
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          ส่งรายงานผลการนิเทศ
        </VBtn>
      </VCol>
    </div>

    <!--  report visit Form Dialog -->
    <VDialog
      v-model="isDialogVisible"
      class="v-dialog-sm"
      style="z-index: 2001 !important"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" absolute />
      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มส่งรายงานผลการปฏิบัติงาน">
        <VCardItem>
          <VForm
            ref="refPlanForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="report_file">
                  ไฟล์แบบแจ้งยืนยันตอบรับการนิเทศ บธส07
                </label>
                <VFileInput
                  v-model="visit_report.report_file"
                  :rules="[requiredValidator]"
                  label="Upload File"
                  persistent-placeholder
                />
                <!--  -->
              </VCol>

              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="report2_file">
                  ไฟล์ใบรายงานผลการนิเทศ บธส08
                </label>
                <VFileInput
                  v-model="visit_report.report2_file"
                  :rules="[requiredValidator]"
                  label="Upload File"
                  persistent-placeholder
                />
                <!--  -->
              </VCol>

              <!-- report_file2 -->
              <!-- send_report_at -->
              <!-- report_status_id -->
              <!-- visit_detail -->
              <!-- is_recruit -->

              <VCol cols="12">
                <label class="v-label" for="workplace_address"> หมายเหตุ</label>
                <AppTextField
                  id="visit_detail"
                  v-model="visit_report.visit_detail"
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="workplace_province_id">
                  ต้องการรับนักศึกษาในรอบหน้าหรือไม่?
                </label>
                <AppSelect
                  :items="selectOptions.recruits"
                  v-model="visit_report.is_recruit"
                  variant="outlined"
                  placeholder="Is Recruit"
                  clearable
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn type="submit" @click="onSubmit" id="btn-submit">
            <span>Save</span></VBtn
          >
        </VCardText>
      </VCard>
    </VDialog>
    <!--  -->
  </div>
</template>
