<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import "dayjs/locale/th";
import "sweetalert2/src/sweetalert2.scss";
import { useRoute, useRouter } from "vue-router";

import { PDFDocument, rgb } from "pdf-lib";
import fontkit from "@pdf-lib/fontkit";

////
import buddhistEra from "dayjs/plugin/buddhistEra";
import "vue3-pdf-app/dist/icons/main.css";
import { useCwieDataStore } from "./useCwieDataStore";

import { form_statuses, text_statuses, statusShow } from "@/data-constant/data";

// const route = useRoute();
dayjs.extend(buddhistEra);
const route = useRoute();
const router = useRouter();
const cwieDataStore = useCwieDataStore();

const student = ref({});
const item = ref({});
const response_company = ref({
  response_document_file: [],
  status_id: "",
  response_send_at: "",
  province_id: null,
});

const plan = ref({
  plan_document_file: [],
  plan_send_at: "",
  workplace_address: "",
  workplace_province_id: "",
  workplace_amphur_id: "",
  workplace_tumbol_id: "",
  workplace_googlemap_url: "",
  workplace_googlemap_file: "",
});

const chairman = ref({
  prefix: "",
  firstname: "",
  surname: "",
  signature_file: "",
  executive_position: "",
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

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const orderBy = ref("id");
const order = ref("desc");
const isAdd = ref(true);
const isCheck = ref(true);
const isDialogCheckVisible = ref(false);
const isDialogResponseVisible = ref(false);
const isDialogPlanVisible = ref(false);

const isPlanFormValid = ref(false);

const currentStep = ref(0);
const formSteps = [
  {
    title: "ข้อมูลใบสมัคร",
    size: 24,
    icon: "tabler-file",
  },
  {
    title: "ข้อมูลเอกสารตอบรับ",
    size: 24,
    icon: "tabler-checklist",
    isActiveStepValid: false,
  },
  {
    title: "ข้อมูลแผนการปฏิบัติงาน",
    size: 24,
    icon: "tabler-notes",
  },
];
const items = ref([]);
const formActive = ref({});
const prependIcon = "tabler-arrow-big-right-filled";
const qualifications = [
  {
    title:
      "ผ่านรายวิชาเตรียมสหกิจศึกษา หรืออยู่ระหว่างเรียนวิชาเตรียมสหกิจศึกษา",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "ผ่านรายวิชาหมวดศึกษาทั่วไปและหมวดวิชาเฉพาะ ไม่น้อยกว่า 15, 60 หน่วยกิต ตามลำดับ",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "มีเกรดเฉลี่ยไม่ต่ำกว่า 2.00 นับถึงภาคการศึกษาสุดท้ายก่อนออกฝึกสหกิจศึกษา",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title:
      "ไม่อยู่ในระหว่างถูกพักการศึกษาในภาคการศึกษาที่เข้าร่วมโครงการสหกิจศึกษา",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title: "ไม่เคยถูกลงโทษทางวินัย หรือกระทำผิดกฏหมายอาญาร้ายแรง",
    props: {
      prependIcon: prependIcon,
    },
  },
  {
    title: "ไม่เป็นโรคที่เป็นอุปสรรคต่อการปฏิบัติงานในสถานประกอบการ",
    props: {
      prependIcon: prependIcon,
    },
  },
];

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
const isResponseFormValid = ref(false);
const refForm = ref();
const refResponseForm = ref();
const currentTab = ref(0);
const check = ref(true);

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
          } else {
            isCheck.value = false;
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
      username: userData.username.slice(1, userData.username.length),
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

        // student.value.status = 1;

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
          //   student.value.major_id &&
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
        } else {
          isCheck.value = false;
        }

        // if (isCheck == false) {
        //   router.push({
        //     name: "student-personal-data",
        //   });
        // }
        // currentStep.value = 2;
        // console.log(currentStep);
        fetchStudentDocuments();
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

const fetchForms = () => {
  cwieDataStore
    .fetchForms({
      student_id: student.value.id,
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      includeAll: true,
    })
    .then(async (response) => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data;

        for (let i = 0; i < data.length; i++) {
          if (data[i].active == 1) {
            response_company.value.form_id = data[i].id;
            formActive.value = data[i];

            await cwieDataStore
              .fetchTeacher({ id: formActive.value.chairman_id })
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

            await cwieDataStore
              .fetchCompany({ id: formActive.value.company_id })
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
          }

          await cwieDataStore
            .fetchMajorHeads({
              semester_id: data[i].semester_id,
              major_id: student.value.major_id,
              active: 1,
              includeAll: true,
            })
            .then((res) => {
              data[i].major_head_name = res.data.data[0].teacher_name;
            });
        }
        items.value = data;

        if (items.value.length != 0) {
          isAdd.value = false;
          if (items.value[0].status_id == 6 || items.value[0].status_id == 8) {
            isAdd.value = true;
          }
        }

        totalPage.value = response.data.totalPage;
        totalItems.value = response.data.totalData;
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

const onAddClick = () => {
  if (isCheck.value == false) {
    isDialogCheckVisible.value = true;
  } else {
    router.push({ name: "student-cwie-data-add" });
  }
};

// Response
const onResponseSubmit = () => {
  isOverlay.value = true;
  refResponseForm.value?.validate().then(({ valid }) => {
    if (valid) {
      cwieDataStore
        .addResponseBook({
          ...response_company.value,
          id: response_company.value.form_id,
          response_document_file:
            response_company.value.response_document_file.length !== 0
              ? response_company.value.response_document_file[0]
              : null,
          response_send_at: dayjs().format("YYYY-MM-DD"),
          status_id: response_company.value.status_id,
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("updated", 1);
            nextTick(() => {
              fetchStudent();
              fetchForms();
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
  console.log(plan.value);
};

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

  existingPage.drawText(formActive.value.term.toString(), {
    x: 285, //คอลัมน์ ซ้ายไปขวา
    y: 692, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.semester_year, {
    x: 315, //คอลัมน์ ซ้ายไปขวา
    y: 692, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    student.value.prefix_name +
      student.value.firstname +
      " " +
      student.value.surname,
    {
      x: 220,
      y: 658,
      ...defaultSize,
    }
  );
  existingPage.drawText(student.value.student_code, {
    x: 200,
    y: 638,
    ...defaultSize,
  });

  existingPage.drawText(student.value.class_year.toString(), {
    x: 433,
    y: 638,
    ...defaultSize,
  });

  existingPage.drawText(student.value.class_room, {
    x: 500,
    y: 638,
    ...defaultSize,
  });

  existingPage.drawText(student.value.advisor_name, {
    x: 150,
    y: 616,
    ...defaultSize,
  });

  existingPage.drawText(student.value.major_name, {
    x: 360, //คอลัมน์ ซ้ายไปขวา
    y: 616, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(student.value.tel, {
    x: 105,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(student.value.email, {
    x: 290,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(student.value.gpa.toString(), {
    x: 505,
    y: 595,
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.term.toString(), {
    x: 343,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.semester_year, {
    x: 370,
    y: 574,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(formActive.value.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 455,
      y: 574,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(formActive.value.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 109,
      y: 553,
      ...defaultSize,
    }
  );

  existingPage.drawText(formActive.value.round_no.toString(), {
    x: 260,
    y: 553,
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.company_name, {
    x: 230,
    y: 523,
    ...defaultSize,
  });

  existingPage.drawText(student.value.address, {
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

  existingPage.drawText(company.value.tel, {
    x: 250,
    y: 439,
    ...defaultSize,
  });

  existingPage.drawText(company.value.email, {
    x: 380,
    y: 439,
    ...defaultSize,
  });

  existingPage.drawText(student.value.contact1_name, {
    x: 270,
    y: 406,
    ...defaultSize,
  });

  existingPage.drawText(student.value.contact1_relation, {
    x: 140,
    y: 385,
    ...defaultSize,
  });

  existingPage.drawText(student.value.contact1_tel, {
    x: 275,
    y: 385,
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.request_name, {
    x: 130,
    y: 330,
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.request_position, {
    x: 400,
    y: 330,
    ...defaultSize,
  });

  existingPage.drawText(
    student.value.prefix_name +
      student.value.firstname +
      " " +
      student.value.surname,
    {
      x: 360,
      y: 218,
      ...defaultSize,
    }
  );

  //   const sigUrl = chairman.value.signature_file;
  //   const sigImageBytes = await fetch(sigUrl).then((res) => res.arrayBuffer());
  //   const sigImage = await pdfDoc.embedPng(sigImageBytes);
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

  existingPage.drawText(formActive.value.request_document_number, {
    x: 125, //คอลัมน์ ซ้ายไปขวา
    y: 757, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(formActive.value.request_document_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 335,
      y: 668,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    formActive.value.request_name +
      " (" +
      formActive.value.request_position +
      ")",
    {
      x: 105,
      y: 595,
      ...defaultSize,
    }
  );

  existingPage.drawText(formActive.value.company_name, {
    x: 105, //คอลัมน์ ซ้ายไปขวา
    y: 565, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(student.value.major_name, {
    x: 210, //คอลัมน์ ซ้ายไปขวา
    y: 365, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.term.toString(), {
    x: 291,
    y: 345,
    ...defaultSize,
  });

  existingPage.drawText(formActive.value.semester_year, {
    x: 358,
    y: 345,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(formActive.value.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 434,
      y: 345,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(formActive.value.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 95,
      y: 324,
      ...defaultSize,
    }
  );

  existingPage.drawText(student.value.firstname, {
    x: 104,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(student.value.surname, {
    x: 248,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(student.value.class_year.toString(), {
    x: 390,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(student.value.student_code, {
    x: 460,
    y: 303,
    ...defaultSize,
  });

  existingPage.drawText(student.value.email, {
    x: 170,
    y: 283,
    ...defaultSize,
  });

  existingPage.drawText(student.value.tel, {
    x: 410,
    y: 283,
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(formActive.value.max_response_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 75,
      y: 198,
      ...defaultSize,
    }
  );

  //   Chairman_id

  //   existingPage.drawText(
  //     chairman.value.prefix +
  //       " " +
  //       chairman.value.firstname +
  //       " " +
  //       chairman.value.surname,
  //     {
  //       x: 300,
  //       y: 135,
  //       ...defaultSize,
  //     }
  //   );
  const sigUrl = chairman.value.signature_file;
  const sigImageBytes = await fetch(sigUrl).then((res) => res.arrayBuffer());
  const sigImage = await pdfDoc.embedPng(sigImageBytes);
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

  existingPage.drawText(formActive.value.request_document_number, {
    x: 125, //คอลัมน์ ซ้ายไปขวา
    y: 757, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(formActive.value.request_document_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 335,
      y: 668,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    formActive.value.request_name +
      " (" +
      formActive.value.request_position +
      ")",
    {
      x: 105,
      y: 595,
      ...defaultSize,
    }
  );

  existingPage.drawText(formActive.value.company_name, {
    x: 220, //คอลัมน์ ซ้ายไปขวา
    y: 523, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(student.value.major_name, {
    x: 280, //คอลัมน์ ซ้ายไปขวา
    y: 502, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  });

  existingPage.drawText(
    dayjs(formActive.value.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 165,
      y: 460,
      ...defaultSize,
    }
  );

  existingPage.drawText(
    dayjs(formActive.value.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 290,
      y: 460,
      ...defaultSize,
    }
  );

  existingPage.drawText(student.value.firstname, {
    x: 104,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(student.value.surname, {
    x: 248,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(student.value.class_year.toString(), {
    x: 390,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(student.value.student_code, {
    x: 460,
    y: 418,
    ...defaultSize,
  });

  existingPage.drawText(student.value.email, {
    x: 170,
    y: 397,
    ...defaultSize,
  });

  existingPage.drawText(student.value.tel, {
    x: 410,
    y: 397,
    ...defaultSize,
  });

  const sigUrl = chairman.value.signature_file;
  const sigImageBytes = await fetch(sigUrl).then((res) => res.arrayBuffer());
  const sigImage = await pdfDoc.embedPng(sigImageBytes);
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

const responseProvinceName = (response_province_id) => {
  if (response_province_id) {
    let response_province_select = selectOptions.value.provinces.find((x) => {
      return x.value == response_province_id;
    });
    return response_province_select.title;
  } else {
    return "-";
  }
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchProvinces();
  fetchTeachers();
  fetchDocumentTypes();
});
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
        <VCard title="" class="pa-3">
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
            <VChip label :color="form_statuses[student.status_id]"
              >{{ text_statuses[student.status_id] }}
            </VChip>
          </VCardText>
          <VDivider class="ml-4 mr-4" />
          <VCardText>
            <span class="font-weight-bold">การเข้าใช้งาน : </span>
            <VChip label color="success">active</VChip>
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
                    <span>อำเภอ : </span>
                    <span>{{ student.amphur_name }} </span>
                  </VCol>
                  <VCol cols="12" md="4">
                    <span>ตำบล : </span>
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
                    <!-- <iframe
                      v-if="d.document_file_old != null"
                      :src="d.document_file_old"
                      style="width: 100%; height: 500px"
                    ></iframe> -->

                    <!-- <span v-else> <br />- </span> -->
                    <!-- <vue-pdf-app
                      :id="'pdf' + index"
                      style="height: 500px"
                      :pdf="d.document_file_old"
                    ></vue-pdf-app> -->
                  </VCol>
                </VRow>
              </VWindowItem>
            </VWindow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="12">
        <VBtn color="success" :disabled="!isAdd" @click="onAddClick">
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          เพิ่มใบสมัคร</VBtn
        >

        <VBtn color="primary" class="ml-2" @click="generateRegisPDF">
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          ใบสมัครโครงการ</VBtn
        >

        <VBtn color="primary" class="ml-2" @click="generatePDF">
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          หนังสือขอความอนุเคราะห์</VBtn
        >

        <VBtn color="primary" class="ml-2" @click="generateSendPDF">
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          ></VIcon>
          หนังสือส่งตัว</VBtn
        >
      </VCol>

      <VCol cols="12" md="12" v-for="(it, index) in items">
        <VCard class="pa-5">
          <VCardText>
            <h3>ครั้งที่ {{ items.length - index }}</h3>
          </VCardText>
          <VCardText>
            <AppStepper
              v-model:current-step="currentStep"
              class="checkout-stepper"
              :items="formSteps"
              :isActiveStepValid="true"
              :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
            />
          </VCardText>

          <VDivider></VDivider>
          <VCardText>
            <!-- 👉 stepper content -->
            <VWindow v-model="currentStep" class="disable-tab-transition">
              <VWindowItem>
                <VRow>
                  <!-- <VCol cols="12" md="12" class="d-flex">
                    <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                    <h4 class="pt-1 pl-1">ข้อมูลทั่วไป</h4>
                  </VCol> -->

                  <VCol cols="12" md="6">
                    <!-- <VCol cols="12" md="3"> -->
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip label :color="form_statuses[it.status_id]">
                        {{
                          statusShow(
                            it.status_id,
                            it.request_document_date,
                            it.confirm_response_at
                          )
                        }}</VChip
                      >
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <!-- <VCol cols="12" md="3"> -->
                    <span>ปีการศึกษา : </span>
                    <span>
                      {{ it.semester_name }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ประธานอาจารย์นิเทศประจำสาขา : </span>
                    <span>
                      {{ it.major_head_name }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <!-- <VCol cols="12" md="3"> -->
                    <span>อาจารย์นิเทศ : </span>
                    <span>
                      {{ it.supervision_name }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <!-- <VCol cols="12" md="3"> -->
                    <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                    <span>
                      {{
                        dayjs(it.start_date).locale("th").format("DD MMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                    <span>
                      {{
                        dayjs(it.end_date).locale("th").format("DD MMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VDivider class="mt-4 mb-4"></VDivider>

                  <VCol cols="12" md="12">
                    <span>สถานประกอบการ : </span>
                    <span>
                      {{ it.company_name }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ชื่อ-สกุล ผู้ประสานงาน : </span>
                    <span>
                      {{ it.co_name }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ตำแหน่งผู้ประสานงาน : </span>
                    <span>
                      {{ it.co_position }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>เบอร์โทรศัพท์ : </span>
                    <span>
                      {{ it.co_tel }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>email : </span>
                    <span>
                      {{ it.co_email }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ชื่อ-สกุลผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                    <span>
                      {{ it.request_name }}
                    </span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ตำแหน่งผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                    <span>
                      {{ it.request_position }}
                    </span>
                  </VCol>

                  <VDivider class="mt-6 mb-6"></VDivider>

                  <VCol cols="12" md="6">
                    <h4>คุณสมบัติผู้สมัครโครงการสหกิจศึกษา</h4>
                    <VList :items="qualifications" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VRow>
                      <VCol cols="12" md="12">
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>
                    <VRow v-for="(rl, index) in it.reject_log">
                      <VCol cols="12" md="4" v-if="rl.reject_status_id < 4">
                        <h4 class="mb-0 d-inline mr-1">วันที่ :</h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span
                        >
                      </VCol>
                      <VCol cols="12" md="8" v-if="rl.reject_status_id < 4">
                        <h4 class="mb-0 d-inline mr-1">รายละเอียด :</h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>
                    </VRow>
                  </VCol>
                  <VCol class="text-center">
                    <VBtn
                      color="info"
                      :disabled="it.status_id != 1 || index != 0"
                      :to="{
                        name: 'student-cwie-data-edit-id',
                        params: { id: it.id },
                      }"
                    >
                      Edit
                    </VBtn>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol cols="12" md="6">
                    <!-- <VCol cols="12" md="3"> -->
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip label :color="form_statuses[it.status_id]">
                        {{
                          statusShow(
                            it.status_id,
                            it.request_document_date,
                            it.confirm_response_at
                          )
                        }}</VChip
                      >
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>หนังสือขอความอนุเคราะห์ : </span>
                    <span>
                      {{
                        it.request_document_date == null
                          ? "-"
                          : dayjs(it.request_document_date)
                              .locale("th")
                              .format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>วันที่ต้องตอบรับเอกสาร : </span>
                    <span>{{
                      it.max_response_date == null
                        ? "-"
                        : dayjs(it.max_response_date)
                            .locale("th")
                            .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ไฟล์หนังสือตอบกลับ : </span>
                    <a
                      :href="
                        it.response_document_file != null
                          ? it.response_document_file
                          : '#'
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

                  <VCol cols="12" md="6">
                    <span>วันที่ส่งหนังสือตอบกลับ : </span>
                    <span>{{
                      it.response_send_at == null
                        ? "-"
                        : dayjs(it.response_send_at)
                            .locale("th")
                            .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>จังหวัดที่ตอบรับสหกิจศึกษา : </span>
                    <span>{{
                      responseProvinceName(it.response_province_id)
                    }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>วันที่ตรวจสอบหนังสือตอบกลับ : </span>
                    <span>{{
                      it.confirm_response_at == null
                        ? "-"
                        : dayjs(it.confirm_response_at)
                            .locale("th")
                            .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>หนังสือส่งตัว : </span>
                    <span>
                      {{
                        it.send_document_date == null
                          ? "-"
                          : dayjs(it.send_document_date)
                              .locale("th")
                              .format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VDivider></VDivider>

                  <VCol cols="12" md="6">
                    <VRow>
                      <VCol cols="12" md="12">
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>
                    <VRow v-for="(rl, index) in it.reject_log">
                      <VCol cols="12" md="4" v-if="rl.reject_status_id > 3">
                        <h4 class="mb-0 d-inline mr-1">วันที่ :</h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span
                        >
                      </VCol>
                      <VCol cols="12" md="8" v-if="rl.reject_status_id > 3">
                        <h4 class="mb-0 d-inline mr-1">รายละเอียด :</h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>
                    </VRow>
                  </VCol>

                  <VCol cols="12" md="12" class="text-center">
                    <VBtn
                      color="info"
                      :disabled="
                        it.request_document_date == null ||
                        it.status_id != 5 ||
                        index != 0
                      "
                      @click="isDialogResponseVisible = true"
                    >
                      อัพโหลดเอกสารตอบรับ
                    </VBtn>
                  </VCol>
                </VRow>
              </VWindowItem>

              <VWindowItem>
                <VRow>
                  <VCol cols="12" md="6">
                    <!-- <VCol cols="12" md="3"> -->
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip label :color="form_statuses[it.status_id]">
                        {{
                          statusShow(
                            it.status_id,
                            it.request_document_date,
                            it.confirm_response_at
                          )
                        }}</VChip
                      >
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>หนังสือขอความอนุเคราะห์ : </span>
                    <span>
                      {{
                        it.request_document_date == null
                          ? "-"
                          : dayjs(it.request_document_date)
                              .locale("th")
                              .format("DD MMMM BBBB")
                      }}
                    </span>
                  </VCol>
                  <VCol cols="12" md="6">
                    <span>วันที่ต้องตอบรับเอกสาร : </span>
                    <span>{{
                      it.max_response_date == null
                        ? "-"
                        : dayjs(it.max_response_date)
                            .locale("th")
                            .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>ไฟล์หนังสือตอบกลับ : </span>
                    <a
                      :href="
                        it.response_document_file != null
                          ? it.response_document_file
                          : '#'
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

                  <VCol cols="12" md="6">
                    <span>วันที่ส่งหนังสือตอบกลับ : </span>
                    <span>{{
                      it.response_send_at == null
                        ? "-"
                        : dayjs(it.response_send_at)
                            .locale("th")
                            .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol cols="12" md="6">
                    <span>จังหวัดที่ตอบรับสหกิจศึกษา : </span>
                    <span>{{
                      responseProvinceName(it.response_province_id)
                    }}</span>
                  </VCol>

                  <VDivider></VDivider>

                  <VCol cols="12" md="6">
                    <VRow>
                      <VCol cols="12" md="12">
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>
                    <VRow v-for="(rl, index) in it.reject_log">
                      <VCol cols="12" md="4" v-if="rl.reject_status_id > 4">
                        <h4 class="mb-0 d-inline mr-1">วันที่ :</h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span
                        >
                      </VCol>
                      <VCol cols="12" md="8" v-if="rl.reject_status_id > 4">
                        <h4 class="mb-0 d-inline mr-1">รายละเอียด :</h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>
                    </VRow>
                  </VCol>

                  <VCol cols="12" md="12" class="text-center">
                    <!-- Disabled = true -->
                    <VBtn
                      color="info"
                      :disabled="
                        it.plan_send_at != null ||
                        it.status_id != 7 ||
                        index != 0
                      "
                      @click="isDialogPlanVisible = true"
                    >
                      อัพโหลดแผนการปฏิบัติงาน
                    </VBtn>
                  </VCol>
                </VRow>
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

              <!-- <VBtn
                color="success"
                append-icon="tabler-check"
               
              >
                submit
              </VBtn> -->

              <VBtn
                v-if="formSteps.length - 1 !== currentStep"
                @click="currentStep++"
                :disabled="
                  it.status_id < 5 ||
                  it.reject_status_id == 1 ||
                  it.reject_status_id == 2 ||
                  it.reject_status_id == 3
                "
              >
                Next
                <VIcon icon="tabler-chevron-right" end class="flip-in-rtl" />
              </VBtn>
            </div>
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

    <!-- Del Dialog -->
    <VDialog v-model="isDialogCheckVisible" class="v-dialog-sm" persistent>
      <!-- Dialog Content -->
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
    <VDialog v-model="isDialogResponseVisible" class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogResponseVisible = !isDialogResponseVisible"
        absolute
      />
      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มอัพโหลดเอกสารตอบรับ">
        <VCardItem>
          <VForm
            ref="refResponseForm"
            v-model="isResponseFormValid"
            @submit.prevent="onResponseSubmit"
          >
            <VRow>
              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="status_id">
                  ผลการตอบกลับจากสถานประกอบการ
                </label>
                <AppSelect
                  :items="[
                    { title: 'ตอบรับ', value: 7 },
                    { title: 'ปฏิเสธ', value: 6 },
                    { title: 'สละสิทธิ์', value: 8 },
                  ]"
                  v-model="response_company.status_id"
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
            <span>Save</span></VBtn
          >
        </VCardText>
      </VCard>
    </VDialog>

    <!--  Plan Form Dialog -->
    <VDialog v-model="isDialogPlanVisible" class="v-dialog-sm">
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

              <VCol cols="12">
                <label class="v-label" for="response_province_id">
                  อำเภอ
                </label>
                <AppSelect
                  :items="selectOptions.provinces"
                  v-model="response_company.province_id"
                  variant="outlined"
                  placeholder="Province"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label" for="response_province_id"> ตำบล </label>
                <AppSelect
                  :items="selectOptions.provinces"
                  v-model="response_company.province_id"
                  variant="outlined"
                  placeholder="Province"
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

              <VCol cols="12">
                <!--  -->
                <label class="v-label" for="workplace_googlemap_file">
                  ไฟล์ภาพ Google Map
                </label>
                <VFileInput
                  v-model="plan.workplace_googlemap_file"
                  label="Upload File"
                  persistent-placeholder
                />
                <!--  -->
              </VCol>
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
            <span>Save</span></VBtn
          >
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
