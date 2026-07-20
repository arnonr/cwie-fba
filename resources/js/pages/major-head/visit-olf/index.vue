<script setup>
import { class_rooms, class_years, statuses, form_statuses, statusShow, visit_status } from "@/data-constant/data"
import { useStudentStore } from "./useStudentStore"

import dayjs from "dayjs"
import "dayjs/locale/th"
import buddhistEra from "dayjs/plugin/buddhistEra"

dayjs.extend(buddhistEra)

const studentStore = useStudentStore()

const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const totalItems = ref(0)
const items = ref([])
const isOverlay = ref(true)
const orderBy = ref("student.id")
const order = ref("desc")
const teacherData = JSON.parse(localStorage.getItem("teacherData"))
const semester = ref([])
const major = ref([])

const advancedSearch = reactive({
  semester_id: "",
  student_code: "",
  firstname: "",
  surname: "",
  major_id: "",
  class_year: "",
  class_room: "",

  //   advisor_id: teacherData.id,
  supervision_id: "",
  company_name: "",
  province_id: "",
  visit_status: "",
})

const selectOptions = ref({
  perPage: [
    { title: "20", value: 20 },
    { title: "40", value: 40 },
    { title: "60", value: 60 },
  ],
  orderBy: [{ title: "ลำดับ/level", value: "level" }],
  order: [
    { title: "น้อย -> มาก", value: "asc" },
    { title: "มาก -> น้อย", value: "desc" },
  ],
  provinces: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  blacklists: [
    { title: "None", value: 0 },
    { title: "Blacklist", value: 1 },
  ],
  semesters: [],
  statuses: statuses,
  class_years: class_years,
  class_rooms: class_rooms,
  teachers: [],
  companies: [],
  visit_statuses: [
    { title: "รออการขอออกนิเทศ", value: 1 },
    { title: "ขอออกนิเทศเรียบร้อยแล้ว", value: 2 },
  ],
})

const fetchProvinces = () => {
  studentStore
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

const fetchMajorHeads = () => {
  studentStore
    .fetchMajorHeads({
      teacher_id: teacherData.id,
      perPage: 100,
    })
    .then(response => {
      if (response.status === 200) {
        console.log(response.data.data)
        semester.value = response.data.data.map(r => {
          console.log(r.semester_id)
          
          return r.semester_id
        })

        major.value = response.data.data.map(r => {
          return r.major_id
        })

        fetchSemesters(semester.value)
      } else {
        console.log("error")
      }
    })
    .catch(error => {
      console.error(error)
      isOverlay.value = false
    })
}

fetchMajorHeads()

const fetchSemesters = () => {
  if (semester.value.length != 0) {
    studentStore
      .fetchSemesters({
        id_array: semester.value,
        perPage: 100,
      })
      .then(response => {
        if (response.status === 200) {
          selectOptions.value.semesters = response.data.data.map(r => {
            if (r.is_current == 1) {
              advancedSearch.semester_id = r.id
            }
            
            return {
              title: r.term + "/" + r.semester_year + " รอบที่" + r.round_no,
              value: r.id,
              start_date: r.start_date,
              end_date: r.end_date,
            }
          })

          console.log(selectOptions.value)
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
}

fetchSemesters()

const fetchTeachers = () => {
  studentStore
    .fetchTeachers({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.teachers = response.data.data.map(r => {
          return {
            title: r.prefix + r.firstname + " " + r.surname,
            value: r.id,
          }
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

fetchTeachers()

const fetchMajors = () => {
  studentStore
    .fetchMajors({})
    .then(response => {
      if (response.status === 200) {
        selectOptions.value.majors = response.data.data.map(r => {
          return {
            title: r.name_th,
            value: r.id,
          }
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

fetchMajors()

// 👉 Fetching
const fetchItems = () => {
  let search = {
    ...advancedSearch,
    includeAll: true,
    major_id_array: major.value,
  }

  //   console.log(localStorage.getItem("userData"));

  studentStore
    .fetchListStudents({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      ...search,
      includeForm: true,
      includeVisit: true,
    })
    .then(response => {
      if (response.status === 200) {
        items.value = response.data.data
        totalPage.value = response.data.totalPage
        totalItems.value = response.data.totalData
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

watchEffect(fetchItems)

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value
})

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

const isSnackbarVisible = ref(false)
const snackbarText = ref("")
const snackbarColor = ref("success")

const resolveActive = (active, type) => {
  let data = ""

  if (active == 1) data = ["success", "Active"]

  if (active == 0) data = ["secondary", "In Active"]

  if (type == "color") {
    return data[0]
  }

  return data[1]
}

const resolveBlacklist = (b, type) => {
  let data = ""

  if (b == 1) data = ["error", "Blacklist"]

  if (b == 0) data = ["secondary", "None"]

  if (type == "color") {
    return data[0]
  }

  return data[1]
}

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

if (localStorage.getItem("deleted") == 1) {
  snackbarText.value = "Deleted Company"
  snackbarColor.value = "success"
  isSnackbarVisible.value = true
  localStorage.removeItem("deleted")
}

onMounted(() => {
  window.scrollTo(0, 0)
})
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="ข้อมูลนักศึกษา">
      <VCardItem>
        <VRow class="mt-1 mb-1">
          <!-- Search -->
          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="rowPerPage"
              label="จำนวนรายการ/page"
              density="compact"
              variant="outlined"
              :items="selectOptions.perPage"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="8"
          >
            <VSelect
              v-model="advancedSearch.semester_id"
              label="ปีการศึกษา"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.semesters"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="4"
          >
            <VSelect
              v-model="advancedSearch.visit_status"
              label="สถานะการขอออกนิเทศ"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.visit_statuses"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="2"
          >
            <VTextField
              v-model="advancedSearch.student_code"
              label="รหัสนักศึกษา"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VTextField
              v-model="advancedSearch.firstname"
              label="ชื่อ"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VTextField
              v-model="advancedSearch.surname"
              label="นามสกุล"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol
            cols="12"
            sm="6"
          >
            <VSelect
              v-model="advancedSearch.major_id"
              label="สาขาวิชา"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.majors"
            />
          </VCol>

          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VSelect
              v-model="advancedSearch.class_year"
              label="ชั้นปี"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_years"
            />
          </VCol>

          <VSpacer />
          <VCol
            cols="12"
            sm="3"
          >
            <VSelect
              v-model="advancedSearch.class_room"
              label="ห้อง"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.class_rooms"
            />
          </VCol>
          <!-- 
            <VCol cols="12" sm="6">
            <VSelect
            label="อาจารย์ที่ปรึกษา"
            v-model="advancedSearch.advisor_id"
            density="compact"
            variant="outlined"
            clearable
            :items="selectOptions.teachers"
            />
            </VCol> 
          -->

          <!--
            <VCol cols="12" sm="6">
            <VSelect
            label="อาจารย์นิเทศ"
            v-model="advancedSearch.supervision_id"
            density="compact"
            variant="outlined"
            clearable
            :items="selectOptions.teachers"
            />
            </VCol> 
          -->

          <!--
            <VCol cols="12" sm="6">
            <VTextField
            label="สถานประกอบการ"
            v-model="advancedSearch.company_name"
            density="compact"
            />
            </VCol> 
          -->

          <!--
            <VCol cols="12" sm="6">
            <VSelect
            label="จังหวัดที่ออกปฏิบัติ"
            v-model="advancedSearch.province_id"
            density="compact"
            variant="outlined"
            clearable
            :items="selectOptions.provinces"
            />
            </VCol> 
          -->

          <!-- Table -->

          <VCol
            cols="12"
            sm="12"
          >
            <div style="overflow-x: auto">
              <VTable class="">
                <!-- 👉 table head -->
                <thead>
                  <tr>
                    <!-- <th scope="col" class="font-weight-bold">รหัสนักศึกษา</th> -->
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      ชื่ออาจารย์นิเทศ
                    </th>
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      ชื่อ-นามสกุล
                    </th>
                    <!--
                      <th scope="col" class="text-center font-weight-bold">
                      สาขาวิชา
                      </th> 
                    -->
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      สถานประกอบการ
                    </th>
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      จังหวัด
                    </th>
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      สถานะ
                    </th>
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      สถานะใบขอออกนิเทศ
                    </th>
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      วันที่ออกนิเทศ
                    </th>
                    <th
                      scope="col"
                      class="text-center font-weight-bold"
                    >
                      จัดการ
                    </th>
                  </tr>
                </thead>
                <!-- 👉 table body -->
                <tbody>
                  <tr
                    v-for="it in items"
                    :key="it.id"
                    style="height: 3.75rem"
                  >
                    <!-- 👉 User -->
                    <!--
                      <td>
                      <span>
                      {{ it.student_code }}
                      </span>
                      </td> 
                    -->
                    <td class="text-center">
                      {{ it.supervision_name }}
                    </td>
                    <td class="text-center">
                      {{ it.firstname + " " + it.surname }}
                    </td>
                    <!--
                      <td class="text-center">
                      {{ it.major_name }}
                      </td> 
                    -->

                    <td
                      class="text-center"
                      style="min-width: 100px"
                    >
                      {{ it.company_name }}
                    </td>

                    <td
                      class="text-center"
                      style="min-width: 100px"
                    >
                      {{ responseProvinceName(it.response_province_id) }}
                    </td>

                    <td
                      class="text-center"
                      style="min-width: 100px"
                    >
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
                        }}
                      </VChip>
                    </td>
                    <td class="text-center">
                      <span v-if="it.visit_status">
                        {{ visit_status[it.visit_status].title }}
                      </span>
                    </td>

                    <td
                      class="text-center"
                      style="min-width: 100px"
                    >
                      {{
                        it.visit_date
                          ? dayjs(it.visit_date)
                            .locale("th")
                            .format("DD MMM BB") +
                            " " +
                            it.visit_time
                          : ""
                      }}
                    </td>

                    <!-- 👉 Actions -->
                    <td
                      class="text-center"
                      style="min-width: 80px"
                    >
                      <VBtn
                        v-if="it.visit_id != null"
                        color="success"
                        class="ml-2"
                        :to="{
                          name: 'major-head-visit-view-id',
                          params: { id: it.id },
                        }"
                      >
                        ดูข้อมูล
                      </VBtn>
                    </td>
                  </tr>
                </tbody>

                <!-- 👉 table footer  -->
                <tfoot v-show="!items.length">
                  <tr>
                    <td
                      colspan="7"
                      class="text-center"
                    >
                      No data available
                    </td>
                  </tr>
                </tfoot>
                <tfoot v-show="items.length" />
              </VTable>
            </div>
          </VCol>

          <VCol
            cols="12"
            sm="12"
          >
            <span class="text-sm text-disabled">
              Showing {{ currentPage }} to {{ totalPage }} of
              {{ totalItems }} entries
            </span>
            <VPagination
              v-model="currentPage"
              size="small"
              :total-visible="5"
              :length="totalPage"
            />
          </VCol>
        </VRow>
      </VCardItem>
    </VCard>

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
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
