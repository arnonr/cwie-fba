<script setup>
import { useStudentActionStore } from "./useStudentActionStore";
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import buddhistEra from "dayjs/plugin/buddhistEra";
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { PDFDocument, rgb } from "pdf-lib";
import fontkit from "@pdf-lib/fontkit";
import Swal from "sweetalert2";
import "sweetalert2/src/sweetalert2.scss";
import axios from "@axios";

const studentActionStore = useStudentActionStore();
const props = defineProps(["student_id", "formActive", "student"]);
const emit = defineEmits(["refresh-data", "change-close"]);

const isDialogVisible = ref(false);
const isDialogRejectVisible = ref(false);
const isDialogCheckFalseVisible = ref(false);
const isDialogResponseVisible = ref(false);
const isDialogPlanVisible = ref(false);
const router = useRouter();
const isOverlay = ref(false);
const isResponseFormValid = ref(false);
const provinces = ref([]);
const amphurs = ref([]);
const tumbols = ref([]);

const refResponseForm = ref();

let userData = JSON.parse(localStorage.getItem("userData"));

const isDisabledAdd = ref(false);
const new_status_id = ref(null);
const isCheck = ref(false);
const date = ref({});
const selectOptions = ref({
  provinces: [],
  amphur: [],
  tumbol: [],
});
const rejectLog = ref({
  comment: "",
  reject_status_id: "",
  form_id: "",
  user_id: userData.user_id,
});

const company = ref({
  name_th: "",
  tel: "",
  address: "",
  fax: "",
  email: "",
  province_name: "",
  amphur_name: "",
  tumbol_name: "",
  zipcode: "",
});

const chairman = ref({
  prefix: "",
  firstname: "",
  surname: "",
  signature_file: "",
  executive_position: "",
});

const response_company = ref({
  response_document_file: [],
  status_id: "",
  result: "",
  response_send_at: "",
  province_id: null,
});

const checkFormActive = async () => {
  if (props.formActive != null) {
    if (props.formActive.length != 0) {
      response_company.value.form_id = props.formActive.id;
      isDisabledAdd.value = true;
      if (props.formActive.status_id == 9 || props.formActive.status_id == 10) {
        isDisabledAdd.value = false;
      }

      if (
        props.student.id &&
        props.student.prefix_id &&
        props.student.firstname &&
        props.student.surname &&
        props.student.citizen_id &&
        props.student.address &&
        props.student.province_id &&
        props.student.amphur_id &&
        props.student.tumbol_id &&
        props.student.tel &&
        props.student.email &&
        props.student.faculty_id &&
        // //   props.student.major_id &&
        props.student.class_year &&
        props.student.class_room &&
        props.student.advisor_id &&
        props.student.gpa &&
        props.student.contact1_name &&
        props.student.contact1_relation &&
        props.student.contact1_tel &&
        props.student.blood_group &&
        props.student.emergency_tel &&
        props.student.height &&
        props.student.weight
      ) {
        isCheck.value = true;
      } else {
        isCheck.value = false;
      }
    }

    await studentActionStore
      .fetchTeacher({ id: props.formActive.chairman_id })
      .then((response) => {
        if (response.status === 200) {
          chairman.value = response.data.data;
        } else {
          console.log("error");
        }
      })
      .catch((error) => {
        console.error(error);
        isOverlay.value = false;
      });

    await studentActionStore
      .fetchCompany({ id: props.formActive.company_id })
      .then((response) => {
        if (response.status === 200) {
          company.value = response.data.data;
        } else {
          console.log("error");
        }
      })
      .catch((error) => {
        console.error(error);
        isOverlay.value = false;
      });
  } else {
    isCheck.value = true;
  }
};

watch(props, async () => {
  checkFormActive();
});

onMounted(async () => {
  checkFormActive();
});

// fetch
const fetchProvince = async () => {
  let res = await axios.get("/province", {
    validateStatus: () => true,
  });
  provinces.value = res.data.data;

  selectOptions.value.provinces = res.data.data.map((r) => {
    return { title: r.name_th, value: r.province_id };
  });
};
fetchProvince();

const fetchAmphur = async () => {
  let res = await axios.get("/amphur", {
    validateStatus: () => true,
  });
  amphurs.value = res.data.data;

  selectOptions.value.amphurs = res.data.data.map((r) => {
    return { title: r.name_th, value: r.amphur_id };
  });
};
fetchAmphur();

const fetchTumbol = async () => {
  let res = await axios.get(
    "/tumbol",
    { perPage: 20000 },
    {
      validateStatus: () => true,
    }
  );
  tumbols.value = res.data.data;

  selectOptions.value.tumbols = res.data.data.map((r) => {
    return { title: r.name_th, value: r.tumbol_id };
  });
};
fetchTumbol();

// Function

const generateRegisPDF = async () => {
  isOverlay.value = true;
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );

  let url = "";
  url = window.location.origin + "/storage/pdf/book_regis.pdf";

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
    x: 285, //คอลัมน์ ซ้ายไปขวา
    y: 692, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year, {
    x: 315, //คอลัมน์ ซ้ายไปขวา
    y: 692, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  const getImageOrFallback = (path, fallback) => {
    return new Promise((resolve) => {
      const img = new Image();
      img.src = path;
      img.onload = () => resolve(path);
      img.onerror = () => resolve(fallback);
    });
  };
  const photoUrl = props.student.photo_file;

  if (photoUrl) {
    const link = await getImageOrFallback(photoUrl, "").then(
      (result) => console.log(result) || result
    );

    if (link != "") {
      const photoImageBytes = await fetch(photoUrl).then((res) =>
        res.arrayBuffer()
      );

      let photoImage;
      try {
        photoImage = await pdfDoc.embedPng(photoImageBytes);
      } catch (error) {
        photoImage = await pdfDoc.embedJpg(photoImageBytes);
      }

      existingPage.drawImage(photoImage, {
        x: 473,
        y: 684,
        width: 70,
        height: 90,
      });
    }
  }

  existingPage.drawText(
    props.student.prefix_name +
      props.student.firstname +
      " " +
      props.student.surname,
    {
      x: 220,
      y: 658,
      ...defaultSize,
    }
  );
  existingPage.drawText(props.student.student_code, {
    x: 200,
    y: 638,
    ...defaultSize,
  });

  existingPage.drawText(props.student.class_year.toString(), {
    x: 433,
    y: 638,
    ...defaultSize,
  });

  existingPage.drawText(props.student.class_room, {
    x: 500,
    y: 638,
    ...defaultSize,
  });

  existingPage.drawText(props.student.advisor_name, {
    x: 150,
    y: 616,
    ...defaultSize,
  });

  existingPage.drawText(props.student.major_name, {
    x: 360, //คอลัมน์ ซ้ายไปขวา
    y: 616, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.student.tel, {
    x: 105,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(props.student.email, {
    x: 257,
    y: 596,
    ...defaultSize,
  });

  existingPage.drawText(props.student.gpa.toString(), {
    x: 515,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.term.toString(), {
    x: 343,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year, {
    x: 370,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 455,
      y: 574,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.formActive.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 109,
      y: 553,
      ...defaultSize,
    }
  );

  existingPage.drawText(props.formActive.round_no.toString(), {
    x: 260,
    y: 553,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.company_name, {
    x: 190,
    y: 523,
    ...defaultSize,
  });

  existingPage.drawText(company.value.address, {
    x: 160,
    y: 502,
    ...defaultSize,
  });

  existingPage.drawText(company.value.tumbol_name, {
    x: 130,
    y: 460,
    ...defaultSize,
  });

  existingPage.drawText(company.value.amphur_name, {
    x: 300,
    y: 460,
    ...defaultSize,
  });

  existingPage.drawText(company.value.province_name, {
    x: 455,
    y: 460,
    ...defaultSize,
  });

  existingPage.drawText(company.value.postcode, {
    x: 160,
    y: 439,
    ...defaultSize,
  });

  existingPage.drawText(company.value.tel == null ? "" : company.value.tel, {
    x: 250,
    y: 439,
    ...defaultSize,
  });

  existingPage.drawText(
    company.value.email == null ? "" : company.value.email,
    {
      x: 380,
      y: 439,
      ...defaultSize,
    }
  );

  existingPage.drawText(props.student.contact1_name, {
    x: 270,
    y: 406,
    ...defaultSize,
  });

  existingPage.drawText(props.student.contact1_relation, {
    x: 140,
    y: 385,
    ...defaultSize,
  });

  existingPage.drawText(props.student.contact1_tel, {
    x: 275,
    y: 385,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.request_name, {
    x: 130,
    y: 330,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.request_position, {
    x: 130,
    y: 310,
    ...defaultSize,
  });

  existingPage.drawText(props.student.firstname + " " + props.student.surname, {
    x: 375,
    y: 222,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.chairman_approved_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 375,
      y: 193,
      ...defaultSize,
    }
  );

  const [existingPage2] = await pdfDoc.copyPages(pdfTemplate, [1]);
  pdfDoc.addPage(existingPage2);
  existingPage2.drawText(props.student.advisor_name, {
    x: 250,
    y: 508,
    ...defaultSize,
  });

  existingPage2.drawText(
    dayjs(props.formActive.advisor_verified_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 250,
      y: 482,
      ...defaultSize,
    }
  );
  console.log(props.formActive);
  existingPage2.drawText(props.formActive.major_head_name, {
    x: 250,
    y: 304,
    ...defaultSize,
  });

  existingPage2.drawText(
    dayjs(props.formActive.chairman_approved_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 250,
      y: 278,
      ...defaultSize,
    }
  );

  const pdfBytes = await pdfDoc.save();
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */)
  );

  const link = document.createElement("a");
  link.href = objectPdf;
  //
  link.download = "book.pdf";
  link.click();

  isOverlay.value = false;
};

const generatePDF = async () => {
  isOverlay.value = true;
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );
  let url = "";
  url = window.location.origin + "/storage/pdf/book1.pdf";

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

  existingPage.drawText(props.formActive.request_document_number, {
    x: 80, //คอลัมน์ ซ้ายไปขวา
    y: 757, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.request_document_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 335,
      y: 668,
      ...defaultSize,
    }
  );

  let request_name = "";
  if (props.formActive.request_name == "-") {
    request_name = props.formActive.request_position;
  } else {
    request_name =
      props.formActive.request_name +
      " (" +
      props.formActive.request_position +
      ")";
  }

  existingPage.drawText(request_name, {
    x: 105,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.company_name, {
    x: 105, //คอลัมน์ ซ้ายไปขวา
    y: 565, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.student.major_name, {
    x: 210, //คอลัมน์ ซ้ายไปขวา
    y: 365, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.term.toString(), {
    x: 291,
    y: 345,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.semester_year, {
    x: 358,
    y: 345,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 434,
      y: 345,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.formActive.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 95,
      y: 324,
      ...defaultSize,
    }
  );

  existingPage.drawText(props.student.firstname, {
    x: 104,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(props.student.surname, {
    x: 248,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(props.student.class_year.toString(), {
    x: 390,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(props.student.student_code, {
    x: 460,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(props.student.email, {
    x: 170,
    y: 283,
    ...defaultSize,
  });

  existingPage.drawText(props.student.tel, {
    x: 410,
    y: 283,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.max_response_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 75,
      y: 198,
      ...defaultSize,
    }
  );

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
    y: 120,
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
      x: 300,
      y: 117,
      ...defaultSize,
    }
  );

  existingPage.drawText(chairman.value.executive_position, {
    x: 275,
    y: 96,
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

const generateSendPDF = async () => {
  isOverlay.value = true;
  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf";
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf";
  const fontBytes = await fetch(urlFont).then((res) => res.arrayBuffer());
  const fontBytesBold = await fetch(urlFontBold).then((res) =>
    res.arrayBuffer()
  );
  let url = "";
  url = window.location.origin + "/storage/pdf/book2.pdf";

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

  existingPage.drawText(props.formActive.send_document_number, {
    x: 80, //คอลัมน์ ซ้ายไปขวา
    y: 757, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.send_document_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 335,
      y: 668,
      ...defaultSize,
    }
  );

  let request_name = "";
  if (props.formActive.request_name == "-") {
    request_name = props.formActive.request_position;
  } else {
    request_name =
      props.formActive.request_name +
      " (" +
      props.formActive.request_position +
      ")";
  }

  existingPage.drawText(request_name, {
    x: 105,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(props.formActive.company_name, {
    x: 180, //คอลัมน์ ซ้ายไปขวา
    y: 524, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(props.student.major_name, {
    x: 280, //คอลัมน์ ซ้ายไปขวา
    y: 502, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(props.formActive.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 165,
      y: 460,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(props.formActive.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 290,
      y: 460,
      ...defaultSize,
    }
  );

  existingPage.drawText(props.student.firstname, {
    x: 104,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(props.student.surname, {
    x: 248,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(props.student.class_year.toString(), {
    x: 390,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(props.student.student_code, {
    x: 460,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(props.student.email, {
    x: 155,
    y: 397,
    ...defaultSize,
  });

  existingPage.drawText(props.student.tel, {
    x: 410,
    y: 397,
    ...defaultSize,
  });

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
    y: 150,
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
      x: 300,
      y: 145,
      ...defaultSize,
    }
  );

  existingPage.drawText(chairman.value.executive_position, {
    x: 275,
    y: 125,
    ...defaultSize,
  });

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

const onAddClick = () => {
  if (isCheck.value == false) {
    isDialogCheckFalseVisible.value = true;
  } else {
    router.push({ name: "student-cwie-data-add" });
  }
};

const confirmCancel = (it) => {
  Swal.fire({
    title: "Are you sure?",
    text: "เมื่อยกเลิกการสมัครแล้วจะไม่สามารถแก้ไขข้อมูลได้",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Cancel it!",
    customClass: {
      confirmButton:
        "v-btn v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated",
      cancelButton:
        "v-btn v-btn--elevated v-theme--light bg-error v-btn--density-default v-btn--size-default v-btn--variant-elevated ml-1",
    },
    buttonsStyling: false,
  }).then((result) => {
    if (result.isConfirmed) {
      studentActionStore
        .editForm({
          id: props.formActive.id,
          active: 0,
          status_id: 10,
          //
        })
        .then(async (response) => {
          if (response.status == 200) {
            // Swal.fire({
            //   icon: "success",
            //   title: "Cancel!",
            //   text: "Your file has been cancel.",
            //   customClass: {
            //     confirmButton:
            //       "v-btn v-btn--elevated v-theme--light bg-success v-btn--density-default v-btn--size-default v-btn--variant-elevated",
            //   },
            // });
            router.go({ name: "student-cwie-data" });
          } else {
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
        });
    }
  });
};

const onResponseSubmit = () => {
  isOverlay.value = true;
  refResponseForm.value?.validate().then(({ valid }) => {
    if (valid) {
      studentActionStore
        .addResponseBook({
          ...response_company.value,
          id: response_company.value.form_id,
          response_document_file:
            response_company.value.response_document_file.length !== 0
              ? response_company.value.response_document_file[0]
              : null,
          response_send_at: dayjs().format("YYYY-MM-DD"),
          status_id: 7,
          response_result: response_company.value.result,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              router.go({ name: "student-cwie-data" });
              isDialogResponseVisible.value = false;
              snackbarText.value = "Success";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;
            });
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
          isOverlay.value = false;
        });
    } else {
      snackbarText.value = "ข้อมูลไม่ครบถ้วน";
      snackbarColor.value = "error";
      isSnackbarVisible.value = true;
    }
    isOverlay.value = false;
  });
};

const onPlanSubmit = () => {
  studentActionStore
    .addPlan({
      ...plan.value,
      id: response_company.value.form_id,
      plan_document_file:
        plan.value.plan_document_file.length !== 0
          ? plan.value.plan_document_file[0]
          : null,
      workplace_googlemap_file:
        plan.value.workplace_googlemap_file.length !== 0
          ? plan.value.workplace_googlemap_file[0]
          : null,
      plan_send_at: dayjs().format("YYYY-MM-DD"),
    })
    .then((response) => {
      if (response.data.message == "success") {
        localStorage.setItem("updated", 1);
        nextTick(() => {
          router.go({ name: "student-cwie-data" });
          isDialogPlanVisible.value = false;
          snackbarText.value = "Success";
          snackbarColor.value = "success";
          isSnackbarVisible.value = true;
        });
      } else {
        isOverlay.value = false;
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
      isOverlay.value = false;
    });
  ``;
};
</script>
<style scoped>
/* .swal2-container {
  z-index: 20001 !important;
} */
</style>
<template>
  <div>
    <div>
      <VCol cols="12" md="12">
        <VBtn
          color="success"
          :disabled="isDisabledAdd"
          @click="onAddClick"
          id="btnAddForm"
          v-if="userData.account_type == 1"
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          เพิ่มใบสมัคร
        </VBtn>

        <VBtn
          color="primary"
          class="ml-2"
          @click="generateRegisPDF"
          v-if="props.formActive"
          :disabled="
            props.formActive.status_id < 5 ||
            props.formActive.status_id == 9 ||
            props.formActive.status_id == 10
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          ดาวน์โหลดใบสมัครโครงการ
        </VBtn>

        <VBtn
          color="primary"
          class="ml-2"
          @click="generatePDF"
          v-if="props.formActive"
          :disabled="
            props.formActive.status_id < 6 ||
            props.formActive.status_id == 9 ||
            props.formActive.status_id == 10
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          หนังสือขอความอนุเคราะห์
        </VBtn>

        <VBtn
          color="primary"
          class="ml-2"
          @click="generateSendPDF"
          v-if="props.formActive"
          :disabled="
            props.formActive.status_id < 11 ||
            props.formActive.status_id == 9 ||
            props.formActive.status_id == 10
          "
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          หนังสือส่งตัว
        </VBtn>
      </VCol>

      <VCol cols="12" md="12">
        <VBtn
          color="info"
          v-if="props.formActive && userData.account_type == 1"
          :disabled="props.formActive.status_id != 1"
          :to="{
            name: 'student-cwie-data-edit-id',
            params: { id: props.formActive.id },
          }"
        >
          แก้ไขใบสมัคร
        </VBtn>

        <VBtn
          color="error"
          class="ml-2"
          v-if="props.formActive && userData.account_type == 1"
          :disabled="props.formActive.status_id > 5"
          @click="confirmCancel(it)"
        >
          ยกเลิกการสมัคร
        </VBtn>

        <VBtn
          color="info"
          class="ml-2"
          v-if="props.formActive && userData.account_type == 1"
          @click="isDialogResponseVisible = true"
          :disabled="
            props.formActive.request_document_date == null ||
            props.formActive.status_id != 6
          "
        >
          อัพโหลดเอกสารตอบรับ
        </VBtn>

        <VBtn
          color="info"
          class="ml-2"
          v-if="props.formActive && userData.account_type == 1"
          :disabled="props.formActive.status_id != 11"
          @click="isDialogPlanVisible = true"
        >
          อัพโหลดแผนการปฏิบัติงาน
        </VBtn>
      </VCol>
      <VCol cols="12" md="12" v-if="props.formActive">
        <h3 class="text-red" v-if="props.formActive.status_id == 1">
          ผู้อนุมัติส่งข้อมูลกลับให้แก้ไข
          <br />โปรดแก้ไขข้อมูลส่วนตัวหรือข้อมูลใบสมัคร
        </h3>
      </VCol>
    </div>

    <!-- Dialog -->

    <!-- Dialog isDialogCheckFalseVisible -->
    <VDialog v-model="isDialogCheckFalseVisible" class="v-dialog-sm" persistent>
      <VCard title="กรอกข้อมูลส่วนตัวไม่ครบถ้วน">
        <VCardText>โปรดระบุข้อมูลส่วนตัวให้ครบถ้วน</VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            :to="{
              name: 'student-personal-data',
            }"
            color="error"
          >
            Ok</VBtn
          >
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Response Form Dialog -->
    <VDialog
      persistent
      v-if="formActive != null"
      v-model="isDialogResponseVisible"
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogResponseVisible = !isDialogResponseVisible"
        absolute
      />
      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มอัพโหลดเอกสารตอบรับ" style="overflow: visible">
        <VCardItem style="overflow: visible">
          <VForm
            ref="refResponseForm"
            v-model="isResponseFormValid"
            @submit.prevent="onResponseSubmit"
            style="overflow: visible"
          >
            <VRow>
              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="status_id">
                  ผลการตอบกลับจากสถานประกอบการ
                </label>
                <AppSelect
                  style="overflow: visible"
                  :items="[
                    { title: 'ตอบรับ', value: 8 },
                    { title: 'ปฏิเสธ', value: 9 },
                    { title: 'สละสิทธิ์', value: 10 },
                  ]"
                  v-model="response_company.result"
                  :rules="[requiredValidator]"
                  variant="outlined"
                  placeholder="Status"
                  clearable
                />
                <!--  -->
              </VCol>

              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="response_document_file">
                  ไฟล์หนังสือตอบกลับ
                </label>
                <VFileInput
                  v-model="response_company.response_document_file"
                  :rules="[requiredValidator]"
                  label="Upload File"
                  persistent-placeholder
                />
                <!--  -->
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="response_province_id">
                  จังหวัดที่ไปปฏิบัติงาน
                </label>
                <AppSelect
                  :items="selectOptions.provinces"
                  v-model="response_company.province_id"
                  variant="outlined"
                  placeholder="Province"
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
            @click="isDialogResponseVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn type="submit" @click="onResponseSubmit" id="btn-submit">
            <span>Save</span>
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!--  Plan Form Dialog -->
    <VDialog
      v-model="isDialogPlanVisible"
      class="v-dialog-sm"
      v-if="formActive != null"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogPlanVisible = !isDialogPlanVisible"
        absolute
      />
      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มอัพโหลดแผนการปฏิบัติงาน">
        <VCardItem>
          <VForm
            ref="refPlanForm"
            v-model="isPlanFormValid"
            @submit.prevent="onPlanSubmit"
          >
            <VRow>
              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="plan_document_file">
                  ไฟล์แผนการปฏิบัติงาน
                </label>
                <VFileInput
                  v-model="plan.plan_document_file"
                  :rules="[requiredValidator]"
                  label="Upload File"
                  persistent-placeholder
                />
                <!--  -->
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="workplace_address"> Address </label>
                <AppTextField
                  id="workplace_address"
                  v-model="plan.workplace_address"
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="workplace_province_id">
                  จังหวัดที่ไปปฏิบัติงาน
                </label>
                <AppSelect
                  :items="selectOptions.plan_provinces"
                  v-model="plan.workplace_province_id"
                  variant="outlined"
                  placeholder="Province"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="workplace_amphur_id">
                  อำเภอ/เขต
                </label>
                <AppSelect
                  :items="selectOptions.plan_amphurs"
                  v-model="plan.workplace_amphur_id"
                  variant="outlined"
                  placeholder="Amphur"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="workplace_tumbol_id">
                  ตำบล/แขวง
                </label>
                <AppSelect
                  :items="selectOptions.plan_tumbols"
                  v-model="plan.workplace_tumbol_id"
                  variant="outlined"
                  placeholder="Tumbol"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="workplace_googlemap_url">
                  Google Map Url
                </label>
                <AppTextField
                  id="workplace_googlemap_url"
                  v-model="plan.workplace_googlemap_url"
                />
              </VCol>

              <!-- <VCol cols="12">
                <label class="v-label" for="workplace_googlemap_file">
                  ไฟล์ภาพ Google Map
                </label>
                <VFileInput
                  v-model="plan.workplace_googlemap_file"
                  label="Upload File"
                  persistent-placeholder
                />
              </VCol> -->
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogPlanVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn type="submit" @click="onPlanSubmit" id="btn-submit">
            <span>Save</span>
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
