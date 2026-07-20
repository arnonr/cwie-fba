<script setup>
import PersonalData from "@/components/student-view/PersonalData.vue"

import Swal from "sweetalert2"
import "sweetalert2/src/sweetalert2.scss"
import { useRoute, useRouter } from "vue-router"
import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"
import "vue3-pdf-app/dist/icons/main.css"
import { useCwieDataStore } from "./useCwieDataStore"
import { form_statuses, statusShow } from "@/data-constant/data"

const props = defineProps(["user_type", "student_id"])

dayjs.extend(buddhistEra)

const cwieDataStore = useCwieDataStore()
const route = useRoute()
const router = useRouter()

let userData = JSON.parse(localStorage.getItem("userData"))

if (props.user_type == "teacher") {
  let userData = JSON.parse(localStorage.getItem("userData"))
}

import { PDFDocument, rgb } from "pdf-lib"
import fontkit from "@pdf-lib/fontkit"

const student = ref({})
const item = ref({})
const formActive = ref({})

const rejectLog = ref({
  comment: "",
  reject_status_id: 3,
  form_id: null,
  user_id: userData.user_id,
})

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
})

const response_company = ref({
  response_document_file: [],
  status_id: "",
  result: "",
  response_send_at: "",
  province_id: null,
})

const chairman = ref({
  prefix: "",
  firstname: "",
  surname: "",
  signature_file: "",
  executive_position: "",
})

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const orderBy = ref("id")
const order = ref("desc")
const isAdd = ref(true)
const isCheck = ref(true)
const isDialogVisible = ref(false)
const isDialogRejectVisible = ref(false)
const isDialogResponseVisible = ref(false)
const isDialogResponseRejectVisible = ref(false)

const currentStep = ref(0)

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
]

const items = ref([])
const prependIcon = "tabler-arrow-big-right-filled"

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
]

const documents_certificate = ref([])

const isOverlay = ref(false)
const isFormValid = ref(false)
const refForm = ref()
const currentTab = ref(0)

const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")

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
})

const fetchProvinces = () => {
  cwieDataStore
    .fetchProvinces({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.provinces = response.data.data.map(r => {
          return { title: r.name_th, value: r.province_id }
        })
        isOverlay.value = false
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

fetchProvinces()

const fetchDocumentTypes = () => {
  cwieDataStore
    .fetchDocumentTypes({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.document_types = response.data.data.map(r => {
          return { title: r.name, value: r.id }
        })

        selectOptions.value.document_types =
          selectOptions.value.document_types.filter(d => {
            return d.value != 1
          })

        fetchStudent()

        isOverlay.value = false
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const fetchStudentDocuments = () => {
  cwieDataStore
    .fetchStudentDocuments({
      student_id: student.value.id,
    })
    .then(response => {
      if (response.status === 200) {
        const { data } = response.data

        let document = data.filter(d => {
          return d.document_type_id != 1
        })

        student.value.documents = student.value.documents.map(d => {
          let index = document.find(e => {
            return d.document_type_id == e.document_type_id
          })
          if (index) {
            d.id = index.id
            d.document_file_old = index.document_file
            d.document_file = []
          } else {
            isCheck.value = false
          }
          
          return d
        })

        // Cert
        let document_cert = data.filter(d => {
          return d.document_type_id == 1
        })

        if (document_cert.length > 0) {
          documents_certificate.value = []

          for (let index = 0; index < document_cert.length; index++) {
            let d = document_cert[index]
            documents_certificate.value.push({
              id: d.id,
              document_type_id: 1,
              document_name: d.document_name,
              student_id: d.student_id,
              document_file_old:
                d.document_file != null ? d.document_file : null,
              document_file: [],
            })
          }
        }

        isOverlay.value = false
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

// let userData = JSON.parse(localStorage.getItem("userData"));

const fetchStudent = () => {
  cwieDataStore
    .fetchStudents({
      id: props.student_id,
      includeAll: true,
    })
    .then(response => {
      if (response.data.message == "success") {
        const { data } = response.data

        student.value = { ...data[0] }

        student.value.faculty_name = student.value.faculty_name.replace(
          "คณะ",
          "",
        )

        student.value.major_name = student.value.major_name
          ? student.value.major_name.replace("สาขาวิชา", "")
          : ""

        // student.value.status = 1;

        student.value.documents = selectOptions.value.document_types.map(
          d => {
            return {
              id: null,
              document_file: null,
              document_file_old: ref(null),
              document_type_id: d.value,
              document_name: d.title,
              student_id: student.value.id,
            }
          },
        )

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
          isCheck.value = false
        }

        // if (isCheck == false) {
        //   router.push({
        //     name: "student-personal-data",
        //   });
        // }
        // currentStep.value = 2;
        // console.log(currentStep);
        fetchStudentDocuments()
        fetchForms()
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const fetchForms = () => {
  cwieDataStore
    .fetchForms({
      student_id: props.student_id,

      //   student_id: student.value.id,
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      includeAll: true,
    })
    .then(async response => {
      // const { rows } = response.data;
      // isOverLay.value = false;
      if (response.data.message == "success") {
        const { data } = response.data

        for (let i = 0; i < data.length; i++) {
          if (data[i].active == 1) {
            response_company.value.form_id = data[i].id
            formActive.value = data[i]

            await cwieDataStore
              .fetchTeacher({ id: formActive.value.chairman_id })
              .then(response => {
                if (response.status === 200) {
                  chairman.value = response.data.data
                } else {
                  console.log("error")
                }
              })
              .catch(error => {
                console.error(error)
                isOverlay.value = false
              })

            await cwieDataStore
              .fetchCompany({ id: formActive.value.company_id })
              .then(response => {
                if (response.status === 200) {
                  company.value = response.data.data
                } else {
                  console.log("error")
                }
              })
              .catch(error => {
                console.error(error)
                isOverlay.value = false
              })
          }

          await cwieDataStore
            .fetchMajorHeads({
              semester_id: data[i].semester_id,
              major_id: student.value.major_id,
              active: 1,
              includeAll: true,
            })
            .then(res => {
              data[i].major_head_name = res.data.data[0].teacher_name
            })
        }
        items.value = data

        if (items.value.length != 0) {
          isAdd.value = false
          if (items.value[0].status_id == 6 || items.value[0].status_id == 8) {
            isAdd.value = true
          }
        }

        totalPage.value = response.data.totalPage
        totalItems.value = response.data.totalData
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

const onRejectSubmit = () => {
  if (rejectLog.comment != "") {
    cwieDataStore
      .addRejectLog({
        ...rejectLog.value,
        active: 1,
      })
      .then(response => {
        if (response.data.message == "success") {
          localStorage.setItem("Rejected", 1)
          nextTick(() => {
            fetchStudent()
            fetchForms()
            snackbarText.value = "Rejected"
            snackbarColor.value = "success"
            isSnackbarVisible.value = true
            isOverlay.value = false
            isDialogRejectVisible.value = false
            isDialogResponseRejectVisible.value = false
          })
        } else {
          isOverlay.value = false
          console.log("error")
        }
      })
      .catch(error => {
        console.error(error)
      })
  } else {
    snackbarText.value = "โปรดระบุเหตุผล"
    snackbarColor.value = "error"
    isSnackbarVisible.value = true
    isOverlay.value = false
  }
}

const status_id_send = ref(5)

const onSubmit = () => {
  let date = {}
  if (status_id_send.value == 5) {
    date = {
      faculty_confirmed_at: dayjs().format("YYYY-MM-DD"),
    }
  } else if (status_id_send.value == 7) {
    date = {
      confirm_response_at: dayjs().format("YYYY-MM-DD"),
    }
    status_id_send.value = item.value.response_result
  } else {
  }

  cwieDataStore
    .approve({
      id: item.value.id,
      status_id: status_id_send.value,
      ...date,
    })
    .then(response => {
      if (response.data.message == "success") {
        localStorage.setItem("Approved", 1)
        nextTick(() => {
          fetchStudent()
          fetchForms()
          snackbarText.value = "Approved"
          snackbarColor.value = "success"
          isSnackbarVisible.value = true
          isOverlay.value = false
          isDialogVisible.value = false
          isDialogResponseVisible.value = false
        })
      } else {
        isOverlay.value = false
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
    })
}

const confirmCancel = it => {
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
  }).then(result => {
    if (result.isConfirmed) {
      cwieDataStore
        .editForm({
          id: it.id,
          active: 0,
          status_id: 10,

          //
        })
        .then(async response => {
          if (response.status == 200) {
            Swal.fire({
              icon: "success",
              title: "Cancel!",
              text: "Your file has been cancel.",
              customClass: {
                confirmButton:
                  "v-btn v-btn--elevated v-theme--light bg-success v-btn--density-default v-btn--size-default v-btn--variant-elevated",
              },
            })
          } else {
            console.log("error")
          }
        })
        .catch(error => {
          console.error(error)
        })
    }
  })
}

const reject_status_show = reject_status_id => {
  if (reject_status_id) {
    if (reject_status_id == 1) {
      return "อาจารย์ที่ปรึกษา"
    }

    if (reject_status_id == 2) {
      return "ประธานอาจารย์นิเทศ"
    }

    if (reject_status_id == 3) {
      return "เจ้าหน้าที่คณะ"
    }

    if (reject_status_id == 4) {
      return "เอกสารตอบรับ"
    }

    if (reject_status_id == 5) {
      return "แผนการปฏิบัติงาน"
    }
  }
  
  return ""
}

onMounted(() => {
  window.scrollTo(0, 0)

  //   fetchProvinces();
  //   fetchTeachers();
  fetchDocumentTypes()
})

const responseProvinceName = response_province_id => {
  if (response_province_id) {
    let response_province_select = selectOptions.value.provinces.find(x => {
      return x.value == response_province_id
    })
    
    return response_province_select.title
  } else {
    return "-"
  }
}

const generateRegisPDF = async () => {
  isOverlay.value = true

  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf"
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf"
  const fontBytes = await fetch(urlFont).then(res => res.arrayBuffer())

  const fontBytesBold = await fetch(urlFontBold).then(res =>
    res.arrayBuffer(),
  )

  let url = ""
  url = window.location.origin + "/storage/pdf/book_regis.pdf"

  const existingPdfBytes = await fetch(url).then(res => res.arrayBuffer())

  const pdfTemplate = await PDFDocument.load(existingPdfBytes)

  // Create PDF
  const pdfDoc = await PDFDocument.create()

  pdfDoc.registerFontkit(fontkit)

  const sarabunFont = await pdfDoc.embedFont(fontBytes)
  const sarabunBoldFont = await pdfDoc.embedFont(fontBytesBold)

  const [existingPage] = await pdfDoc.copyPages(pdfTemplate, [0])

  pdfDoc.addPage(existingPage)

  const defaultSize = {
    size: 16,
    font: sarabunFont,
    color: rgb(0, 0, 0),
  }

  existingPage.drawText(formActive.value.term.toString(), {
    x: 285, //คอลัมน์ ซ้ายไปขวา
    y: 692, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.semester_year, {
    x: 315, //คอลัมน์ ซ้ายไปขวา
    y: 692, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  const photoUrl = student.value.photo_file

  const photoImageBytes = await fetch(photoUrl).then(res =>
    res.arrayBuffer(),
  )

  let photoImage
  try {
    photoImage = await pdfDoc.embedPng(photoImageBytes)
  } catch (error) {
    photoImage = await pdfDoc.embedJpg(photoImageBytes)
  }

  existingPage.drawImage(photoImage, {
    x: 473,
    y: 684,
    width: 70,
    height: 90,
  })

  existingPage.drawText(
    student.value.prefix_name +
      student.value.firstname +
      " " +
      student.value.surname,
    {
      x: 220,
      y: 658,
      ...defaultSize,
    },
  )
  existingPage.drawText(student.value.student_code, {
    x: 200,
    y: 638,
    ...defaultSize,
  })

  existingPage.drawText(student.value.class_year.toString(), {
    x: 433,
    y: 638,
    ...defaultSize,
  })

  existingPage.drawText(student.value.class_room, {
    x: 500,
    y: 638,
    ...defaultSize,
  })

  existingPage.drawText(student.value.advisor_name, {
    x: 150,
    y: 616,
    ...defaultSize,
  })

  existingPage.drawText(student.value.major_name, {
    x: 360, //คอลัมน์ ซ้ายไปขวา
    y: 616, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(student.value.tel, {
    x: 105,
    y: 595,
    ...defaultSize,
  })

  existingPage.drawText(student.value.email, {
    x: 270,
    y: 596,
    ...defaultSize,
  })

  existingPage.drawText(student.value.gpa.toString(), {
    x: 520,
    y: 595,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.term.toString(), {
    x: 343,
    y: 574,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.semester_year, {
    x: 370,
    y: 574,
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 455,
      y: 574,
      ...defaultSize,
    },
  )

  existingPage.drawText(
    dayjs(formActive.value.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 109,
      y: 553,
      ...defaultSize,
    },
  )

  existingPage.drawText(formActive.value.round_no.toString(), {
    x: 260,
    y: 553,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.company_name, {
    x: 230,
    y: 523,
    ...defaultSize,
  })

  existingPage.drawText(student.value.address, {
    x: 160,
    y: 502,
    ...defaultSize,
  })

  existingPage.drawText(company.value.tumbol_name, {
    x: 130,
    y: 460,
    ...defaultSize,
  })

  existingPage.drawText(company.value.amphur_name, {
    x: 300,
    y: 460,
    ...defaultSize,
  })

  existingPage.drawText(company.value.province_name, {
    x: 455,
    y: 460,
    ...defaultSize,
  })

  existingPage.drawText(company.value.postcode, {
    x: 160,
    y: 439,
    ...defaultSize,
  })

  existingPage.drawText(company.value.tel, {
    x: 250,
    y: 439,
    ...defaultSize,
  })

  existingPage.drawText(
    company.value.email == null ? "" : company.value.email,
    {
      x: 380,
      y: 439,
      ...defaultSize,
    },
  )

  existingPage.drawText(student.value.contact1_name, {
    x: 270,
    y: 406,
    ...defaultSize,
  })

  existingPage.drawText(student.value.contact1_relation, {
    x: 140,
    y: 385,
    ...defaultSize,
  })

  existingPage.drawText(student.value.contact1_tel, {
    x: 275,
    y: 385,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.request_name, {
    x: 130,
    y: 330,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.request_position, {
    x: 400,
    y: 330,
    ...defaultSize,
  })

  existingPage.drawText(student.value.firstname + " " + student.value.surname, {
    x: 375,
    y: 243,
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.chairman_approved_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 375,
      y: 215,
      ...defaultSize,
    },
  )

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

  const [existingPage2] = await pdfDoc.copyPages(pdfTemplate, [1])

  pdfDoc.addPage(existingPage2)

  existingPage2.drawText(student.value.advisor_name, {
    x: 250,
    y: 508,
    ...defaultSize,
  })

  existingPage2.drawText(
    dayjs(formActive.value.advisor_verified_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 250,
      y: 482,
      ...defaultSize,
    },
  )

  existingPage2.drawText(formActive.value.major_head_name, {
    x: 250,
    y: 304,
    ...defaultSize,
  })

  existingPage2.drawText(
    dayjs(formActive.value.chairman_approved_at)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 250,
      y: 278,
      ...defaultSize,
    },
  )

  //   existingPage2.drawText(formActive.value.advisor_verified_at, {
  //     x: 375,
  //     y: 243,
  //     ...defaultSize,
  //   });

  //   existingPage2.drawText(formActive.value.teacher.fullname, {
  //     x: 375,
  //     y: 243,
  //     ...defaultSize,
  //   });

  //   existingPage2.drawText(formActive.value.chairman_approved_at, {
  //     x: 375,
  //     y: 243,
  //     ...defaultSize,
  //   });

  const pdfBytes = await pdfDoc.save()
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */),
  )

  const link = document.createElement("a")

  link.href = objectPdf

  //
  link.download = "book.pdf"
  link.click()

  isOverlay.value = false
}

const generatePDF = async () => {
  isOverlay.value = true

  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf"
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf"
  const fontBytes = await fetch(urlFont).then(res => res.arrayBuffer())

  const fontBytesBold = await fetch(urlFontBold).then(res =>
    res.arrayBuffer(),
  )

  let url = ""
  url = window.location.origin + "/storage/pdf/book1.pdf"

  const existingPdfBytes = await fetch(url).then(res => res.arrayBuffer())
  const pdfTemplate = await PDFDocument.load(existingPdfBytes)

  // Create PDF
  const pdfDoc = await PDFDocument.create()

  pdfDoc.registerFontkit(fontkit)

  const sarabunFont = await pdfDoc.embedFont(fontBytes)
  const sarabunBoldFont = await pdfDoc.embedFont(fontBytesBold)

  const [existingPage] = await pdfDoc.copyPages(pdfTemplate, [0])

  pdfDoc.addPage(existingPage)

  const defaultSize = {
    size: 16,
    font: sarabunFont,
    color: rgb(0, 0, 0),
  }

  existingPage.drawText(formActive.value.request_document_number, {
    x: 80, //คอลัมน์ ซ้ายไปขวา
    y: 757, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.request_document_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 335,
      y: 668,
      ...defaultSize,
    },
  )

  let request_name = ""
  if (formActive.value.request_name == "-") {
    request_name = formActive.value.request_position
  } else {
    request_name =
      formActive.value.request_name +
      " (" +
      formActive.value.request_position +
      ")"
  }

  existingPage.drawText(request_name, {
    x: 105,
    y: 595,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.company_name, {
    x: 105, //คอลัมน์ ซ้ายไปขวา
    y: 565, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(student.value.major_name, {
    x: 210, //คอลัมน์ ซ้ายไปขวา
    y: 365, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.term.toString(), {
    x: 291,
    y: 345,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.semester_year, {
    x: 358,
    y: 345,
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 434,
      y: 345,
      ...defaultSize,
    },
  )

  existingPage.drawText(
    dayjs(formActive.value.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 95,
      y: 324,
      ...defaultSize,
    },
  )

  existingPage.drawText(student.value.firstname, {
    x: 104,
    y: 303,
    ...defaultSize,
  })

  existingPage.drawText(student.value.surname, {
    x: 248,
    y: 303,
    ...defaultSize,
  })

  existingPage.drawText(student.value.class_year.toString(), {
    x: 390,
    y: 303,
    ...defaultSize,
  })

  existingPage.drawText(student.value.student_code, {
    x: 460,
    y: 303,
    ...defaultSize,
  })

  existingPage.drawText(student.value.email, {
    x: 170,
    y: 283,
    ...defaultSize,
  })

  existingPage.drawText(student.value.tel, {
    x: 410,
    y: 283,
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.max_response_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 75,
      y: 198,
      ...defaultSize,
    },
  )

  const sigUrl = chairman.value.signature_file
  const sigImageBytes = await fetch(sigUrl).then(res => res.arrayBuffer())

  let sigImage
  try {
    sigImage = await pdfDoc.embedPng(sigImageBytes)
  } catch (error) {
    sigImage = await pdfDoc.embedJpg(sigImageBytes)
  }

  existingPage.drawImage(sigImage, {
    x: 310,
    y: 120,
    width: 100,
    height: 50,
  })

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
    },
  )

  existingPage.drawText(chairman.value.executive_position, {
    x: 275,
    y: 96,
    ...defaultSize,
  })

  const [existingPage2] = await pdfDoc.copyPages(pdfTemplate, [1])

  pdfDoc.addPage(existingPage2)

  const pdfBytes = await pdfDoc.save()
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */),
  )

  const link = document.createElement("a")

  link.href = objectPdf
  link.download = "book.pdf"
  link.click()

  isOverlay.value = false
}

const generateSendPDF = async () => {
  isOverlay.value = true

  const urlFont = window.location.origin + "/storage/THSarabunNew.ttf"
  const urlFontBold = window.location.origin + "/storage/THSarabunNewBold.ttf"
  const fontBytes = await fetch(urlFont).then(res => res.arrayBuffer())

  const fontBytesBold = await fetch(urlFontBold).then(res =>
    res.arrayBuffer(),
  )

  let url = ""
  url = window.location.origin + "/storage/pdf/book2.pdf"

  const existingPdfBytes = await fetch(url).then(res => res.arrayBuffer())
  const pdfTemplate = await PDFDocument.load(existingPdfBytes)

  // Create PDF
  const pdfDoc = await PDFDocument.create()

  pdfDoc.registerFontkit(fontkit)

  const sarabunFont = await pdfDoc.embedFont(fontBytes)
  const sarabunBoldFont = await pdfDoc.embedFont(fontBytesBold)

  const [existingPage] = await pdfDoc.copyPages(pdfTemplate, [0])

  pdfDoc.addPage(existingPage)

  const defaultSize = {
    size: 16,
    font: sarabunFont,
    color: rgb(0, 0, 0),
  }

  existingPage.drawText(formActive.value.send_document_number, {
    x: 80, //คอลัมน์ ซ้ายไปขวา
    y: 757, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.send_document_date)
      .locale("th")
      .format("DD MMMM BBBB"),
    {
      x: 335,
      y: 668,
      ...defaultSize,
    },
  )

  let request_name = ""
  if (formActive.value.request_name == "-") {
    request_name = formActive.value.request_position
  } else {
    request_name =
      formActive.value.request_name +
      " (" +
      formActive.value.request_position +
      ")"
  }

  existingPage.drawText(request_name, {
    x: 105,
    y: 595,
    ...defaultSize,
  })

  existingPage.drawText(formActive.value.company_name, {
    x: 220, //คอลัมน์ ซ้ายไปขวา
    y: 523, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(student.value.major_name, {
    x: 280, //คอลัมน์ ซ้ายไปขวา
    y: 502, //แถว ยิ่งมากยิ่งอยู่ด้านบน
    ...defaultSize,
  })

  existingPage.drawText(
    dayjs(formActive.value.start_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 165,
      y: 460,
      ...defaultSize,
    },
  )

  existingPage.drawText(
    dayjs(formActive.value.end_date).locale("th").format("DD MMMM BBBB"),
    {
      x: 290,
      y: 460,
      ...defaultSize,
    },
  )

  existingPage.drawText(student.value.firstname, {
    x: 104,
    y: 418,
    ...defaultSize,
  })

  existingPage.drawText(student.value.surname, {
    x: 248,
    y: 418,
    ...defaultSize,
  })

  existingPage.drawText(student.value.class_year.toString(), {
    x: 390,
    y: 418,
    ...defaultSize,
  })

  existingPage.drawText(student.value.student_code, {
    x: 460,
    y: 418,
    ...defaultSize,
  })

  existingPage.drawText(student.value.email, {
    x: 170,
    y: 397,
    ...defaultSize,
  })

  existingPage.drawText(student.value.tel, {
    x: 410,
    y: 397,
    ...defaultSize,
  })

  const sigUrl = chairman.value.signature_file
  const sigImageBytes = await fetch(sigUrl).then(res => res.arrayBuffer())

  let sigImage
  try {
    sigImage = await pdfDoc.embedPng(sigImageBytes)
  } catch (error) {
    sigImage = await pdfDoc.embedJpg(sigImageBytes)
  }

  existingPage.drawImage(sigImage, {
    x: 310,
    y: 150,
    width: 100,
    height: 50,
  })

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
    },
  )

  existingPage.drawText(chairman.value.executive_position, {
    x: 275,
    y: 125,
    ...defaultSize,
  })

  const pdfBytes = await pdfDoc.save()
  let objectPdf = URL.createObjectURL(
    new Blob([pdfBytes.buffer], { type: "application/pdf" } /* (1) */),
  )

  const link = document.createElement("a")

  link.href = objectPdf
  link.download = "book.pdf"
  link.click()

  isOverlay.value = false
}
</script>

<template>
  <div>
    <VRow>
      <PersonalData :student_id="props.student_id" />

      <VCol
        cols="12"
        md="12"
      >
        <VBtn
          color="primary"
          class="ml-2"
          :disabled="
            student.status_id < 5 ||
              student.status_id == 9 ||
              student.status_id == 10
          "
          @click="generateRegisPDF"
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          />
          ใบสมัครโครงการ
        </VBtn>

        <VBtn
          color="primary"
          class="ml-2"
          :disabled="
            student.status_id < 6 ||
              student.status_id == 9 ||
              student.status_id == 10
          "
          @click="generatePDF"
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          />
          หนังสือขอความอนุเคราะห์
        </VBtn>

        <VBtn
          color="primary"
          class="ml-2"
          :disabled="
            student.status_id < 11 ||
              student.status_id == 9 ||
              student.status_id == 10
          "
          @click="generateSendPDF"
        >
          <VIcon
            size="16"
            icon="tabler-file-description"
            style="opacity: 1"
            class="mr-1"
          />
          หนังสือส่งตัว
        </VBtn>

        <VBtn
          color="success"
          class="ml-2"
          style="float: right"
          @click="
            router.push({
              path: '/student/cwie-data/edit/' + formActive.id,
              query: {
                student_id: student.id,
              },
            })
          "
        >
          แก้ไขข้อมูลใบสมัคร
        </VBtn>

        <VBtn
          color="warning"
          style="float: right"
          @click="
            router.push({
              path: '/student/personal-data/' + props.student_id,
            })
          "
        >
          แก้ไขข้อมูลทั่วไป
        </VBtn>
      </VCol>

      <VCol
        v-for="(it, index) in items"
        :key="index"
        cols="12"
        md="12"
      >
        <VCard class="pa-5">
          <VCardText>
            <h3>ครั้งที่ {{ items.length - index }}</h3>
          </VCardText>
          <VCardText>
            <AppStepper
              v-model:current-step="currentStep"
              class="checkout-stepper"
              :items="formSteps"
              :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
            />
          </VCardText>

          <VDivider />
          <VCardText>
            <!-- 👉 stepper content -->
            <VWindow
              v-model="currentStep"
              class="disable-tab-transition"
            >
              <VWindowItem>
                <VRow>
                  <!--
                    <VCol cols="12" md="12" class="d-flex">
                    <VIcon size="22" icon="tabler-user" style="opacity: 1" />
                    <h4 class="pt-1 pl-1">ข้อมูลทั่วไป</h4>
                    </VCol> 
                  -->

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip
                        label
                        :color="form_statuses[it.status_id]"
                      >
                        {{
                          statusShow(
                            it.status_id,
                            it.request_document_date,
                            it.confirm_response_at
                          )
                        }}</VChip>
                    </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
                    <span>ปีการศึกษา : </span>
                    <span>
                      {{ it.semester_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ประธานอาจารย์นิเทศประจำสาขา : </span>
                    <span>
                      {{ it.major_head_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
                    <span>อาจารย์นิเทศ : </span>
                    <span>
                      {{ it.supervision_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
                    <span>วันที่เริ่มปฏิบัติสหกิจ : </span>
                    <span>
                      {{
                        dayjs(it.start_date).locale("th").format("DD MMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันสิ้นสุดปฏิบัติสหกิจ : </span>
                    <span>
                      {{
                        dayjs(it.end_date).locale("th").format("DD MMM BBBB")
                      }}
                    </span>
                  </VCol>

                  <VDivider class="mt-4 mb-4" />

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>สถานประกอบการ : </span>
                    <span>
                      {{ it.company_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ชื่อ-สกุล ผู้ประสานงาน : </span>
                    <span>
                      {{ it.co_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ตำแหน่งผู้ประสานงาน : </span>
                    <span>
                      {{ it.co_position }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>เบอร์โทรศัพท์ : </span>
                    <span>
                      {{ it.co_tel }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>email : </span>
                    <span>
                      {{ it.co_email }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ชื่อ-สกุลผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                    <span>
                      {{ it.request_name }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ตำแหน่งผู้เรียนถึง (ใช้ในการออกหนังสือ) : </span>
                    <span>
                      {{ it.request_position }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <span>นามบัตร : </span>
                    <span>
                      <a
                        :href="it.namecard_file"
                        target="_blank"
                      >
                        <VImg
                          :src="it.namecard_file"
                          style="max-width: 300px"
                          class="mt-2"
                        />
                      </a>
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  >
                    <VBtn
                      color="primary"
                      :to="{
                        name: 'cwie-settings-company-edit-id',
                        params: { id: it.company_id },
                      }"
                      target="_blank"
                    >
                      แก้ไขข้อมูลสถานประกอบการ
                    </VBtn>
                  </VCol>

                  <VDivider class="mt-6 mb-6" />

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <h4>คุณสมบัติผู้สมัครโครงการสหกิจศึกษา</h4>
                    <VList :items="qualifications" />
                    <!-- <VCheck  -->
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                    class="text-error"
                  >
                    <VRow>
                      <VCol
                        cols="12"
                        md="12"
                      >
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>

                    <VRow
                      v-for="(rl, index) in it.reject_log"
                      :key="index"
                    >
                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="4"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          วันที่ :
                        </h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="8"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          ผู้ตรวจ :
                        </h4>
                        <span>
                          {{ reject_status_show(rl.reject_status_id) }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="12"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          รายละเอียด :
                        </h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id < 4"
                        cols="12"
                        md="12"
                      >
                        <hr style="border: solid #eee 1px">
                      </VCol>
                    </VRow>
                  </VCol>

                  <VDivider class="mt-6 mb-6" />
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่อาจารย์ที่ปรึกษาอนุมัติ : </span>
                    <span>
                      {{
                        it.advisor_verified_at
                          ? dayjs(it.advisor_verified_at)
                            .locale("th")
                            .format("DD MMM BBBB")
                          : "-"
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ประธานอาจารย์นิเทศอนุมัติ : </span>
                    <span>
                      {{
                        it.chairman_approved_at
                          ? dayjs(it.chairman_approved_at)
                            .locale("th")
                            .format("DD MMM BBBB")
                          : "-"
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่คณะอนุมัติ : </span>
                    <span>
                      {{
                        it.faculty_confirmed_at
                          ? dayjs(it.faculty_confirmed_at)
                            .locale("th")
                            .format("DD MMM BBBB")
                          : "-"
                      }}
                    </span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  >
                    <VBtn
                      color="error"
                      :disabled="it.status_id != 4 || index != 0"
                      @click="
                        () => {
                          rejectLog.form_id = it.id;
                          isDialogRejectVisible = true;
                        }
                      "
                    >
                      <VIcon
                        size="16"
                        icon="tabler-edit"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ส่งกลับให้แก้ไข
                    </VBtn>

                    <VBtn
                      class="ml-2"
                      color="success"
                      :disabled="it.status_id != 4 || index != 0"
                      @click="
                        () => {
                          item = it;
                          isDialogVisible = true;
                        }
                      "
                    >
                      <VIcon
                        size="16"
                        icon="tabler-file-description"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      อนุมัติ
                    </VBtn>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  >
                    <VBtn
                      color="warning"
                      class="ml-2"
                      :disabled="
                        index != 0 || it.status_id == 10 || it.status_id == 9
                      "
                      @click="confirmCancel(it)"
                    >
                      ยกเลิกการสมัคร
                    </VBtn>
                  </VCol>
                </VRow>
              </VWindowItem>
              <VWindowItem>
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <!-- <VCol cols="12" md="3"> -->
                    <span>สถานะฟอร์ม : </span>
                    <span>
                      <VChip
                        label
                        :color="form_statuses[it.status_id]"
                      >
                        {{
                          statusShow(
                            it.status_id,
                            it.request_document_date,
                            it.confirm_response_at
                          )
                        }}</VChip>
                    </span>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
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
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ต้องตอบรับเอกสาร : </span>
                    <span>{{
                      it.max_response_date == null
                        ? "-"
                        : dayjs(it.max_response_date)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ไฟล์หนังสือตอบกลับ : </span>
                    <a
                      v-if="it.response_document_file"
                      :href="it.response_document_file"
                      target="_blank"
                    ><span>
                      <VIcon
                        size="16"
                        icon="tabler-file"
                        style="opacity: 1"
                        class="mr-1"
                      />เอกสาร</span>
                    </a>
                    <span v-else>-</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ส่งหนังสือตอบกลับ : </span>
                    <span>{{
                      it.response_send_at == null
                        ? "-"
                        : dayjs(it.response_send_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>ผลการตอบกลับ : </span>
                    <span>{{
                      it.response_result == null
                        ? "-"
                        : statusShow(it.response_result)
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>จังหวัดที่ตอบรับสหกิจศึกษา : </span>
                    <span>{{
                      responseProvinceName(it.response_province_id)
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <span>วันที่ตรวจสอบหนังสือตอบกลับ : </span>
                    <span>{{
                      it.confirm_response_at == null
                        ? "-"
                        : dayjs(it.confirm_response_at)
                          .locale("th")
                          .format("DD MMMM BBBB")
                    }}</span>
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
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

                  <VDivider />

                  <VCol
                    cols="12"
                    md="6"
                    class="text-error"
                  >
                    <VRow>
                      <VCol
                        cols="12"
                        md="12"
                      >
                        <h4>Remark</h4>
                      </VCol>
                    </VRow>
                    <VRow
                      v-for="(rl, index) in it.reject_log"
                      :key="index"
                    >
                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="4"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          วันที่ :
                        </h4>
                        <span>
                          {{
                            dayjs(rl.created_at)
                              .locale("th")
                              .format("DD MMM BB")
                          }}</span>
                      </VCol>
                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="12"
                      >
                        <h4 class="mb-0 d-inline mr-1 text-error">
                          รายละเอียด :
                        </h4>
                        <span> {{ rl.comment }}</span>
                      </VCol>

                      <VCol
                        v-if="rl.reject_status_id > 3"
                        cols="12"
                        md="12"
                      >
                        <hr style="border: solid #eee 1px">
                      </VCol>
                    </VRow>
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                    class="text-center"
                  >
                    <VBtn
                      color="error"
                      :disabled="it.status_id != 7 || index != 0"
                      @click="
                        () => {
                          rejectLog.form_id = it.id;
                          isDialogResponseRejectVisible = true;
                        }
                      "
                    >
                      <VIcon
                        size="16"
                        icon="tabler-edit"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      ส่งกลับให้แก้ไข
                    </VBtn>

                    <VBtn
                      class="ml-2"
                      color="success"
                      :disabled="it.status_id != 7 || index != 0"
                      @click="
                        () => {
                          item = it;
                          isDialogResponseVisible = true;
                        }
                      "
                    >
                      <VIcon
                        size="16"
                        icon="tabler-file-description"
                        style="opacity: 1"
                        class="mr-1"
                      />
                      รับทราบผล
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
                <VIcon
                  icon="tabler-chevron-left"
                  start
                  class="flip-in-rtl"
                />
                Previous
              </VBtn>
              <VBtn
                v-if="formSteps.length - 1 !== currentStep"
                @click="currentStep++"
              >
                Next
                <VIcon
                  icon="tabler-chevron-right"
                  end
                  class="flip-in-rtl"
                />
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
        <VBtn
          color="error"
          @click="isSnackbarVisible = false"
        >
          Close
        </VBtn>
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

    <!-- Dialog -->
    <VDialog
      v-model="isDialogVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Are You Sure?">
        <VCardText> ยืนยันการอนุมัติ </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="error"
            @click="isDialogVisible = !isDialogVisible"
          >
            Cancel
          </VBtn>
          <VBtn
            color="success"
            @click="onSubmit"
          >
            Approve
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isDialogRejectVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogRejectVisible = !isDialogRejectVisible" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มส่งข้อมูลกลับให้แก้ไข">
        <VCardText>
          <VCol
            cols="12"
            md="12"
            class="align-items-center"
          >
            <label
              class="v-label font-weight-bold"
              for="comment"
            >ระบุเหตุผล :
            </label>
            <AppTextarea
              id="comment"
              v-model="rejectLog.comment"
              rows="5"
              persistent-placeholder
            />
          </VCol>
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="error"
            @click="isDialogRejectVisible = !isDialogRejectVisible"
          >
            Cancel
          </VBtn>
          <VBtn
            color="success"
            @click="onRejectSubmit"
          >
            Reject
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Dialog -->
    <VDialog
      v-model="isDialogResponseVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogResponseVisible = !isDialogResponseVisible" />

      <!-- Dialog Content -->
      <VCard title="Are You Sure?">
        <VCardText> ยืนยันการรับทราบผล </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="error"
            @click="isDialogResponseVisible = !isDialogResponseVisible"
          >
            Cancel
          </VBtn>
          <VBtn
            color="success"
            @click="
              () => {
                status_id_send = 7;
                onSubmit();
              }
            "
          >
            Confirm
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isDialogResponseRejectVisible"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogResponseRejectVisible = !isDialogResponseRejectVisible" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มส่งข้อมูลกลับให้แก้ไข">
        <VCardText>
          <VCol
            cols="12"
            md="12"
            class="align-items-center"
          >
            <label
              class="v-label font-weight-bold"
              for="comment"
            >ระบุเหตุผล :
            </label>
            <AppTextarea
              id="comment"
              v-model="rejectLog.comment"
              rows="5"
              persistent-placeholder
            />
          </VCol>
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="error"
            @click="
              isDialogResponseRejectVisible = !isDialogResponseRejectVisible
            "
          >
            Cancel
          </VBtn>
          <VBtn
            color="success"
            @click="
              () => {
                rejectLog.reject_status_id = 4;
                onRejectSubmit();
              }
            "
          >
            Reject
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
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

.swal2-container {
  z-index: 20001 !important;
}
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
