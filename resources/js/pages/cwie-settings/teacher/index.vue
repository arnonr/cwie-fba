<script setup>
import { requiredValidator } from "@validators";
import { useTeacherStore } from "./useTeacherStore";

const teacherStore = useTeacherStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const item = ref({ firstname: "", surname: "", name: "" });
const isOverlay = ref(true);
const orderBy = ref("id");
const order = ref("asc");

const refForm = ref();
const isFormValid = ref(false);
const isSubmit = ref(false);
const isDialogVisible = ref(false);

const advancedSearch = reactive({
  fullname: null,
  department_id: null,
  active: 1,
});

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
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  departments: [],
});

const fetchDepartments = () => {
  teacherStore
    .fetchDepartments({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.departments = response.data.data.map((r) => {
          return { title: r.name_th, value: r.id };
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
fetchDepartments();

// 👉 Fetching
const fetchItems = () => {
  let search = {
    ...advancedSearch,
    includeDepartment: true,
    includeFaculty: true,
  };

  teacherStore
    .fetchTeachers({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      ...search,
    })
    .then((response) => {
      if (response.status === 200) {
        items.value = response.data.data;
        totalPage.value = response.data.totalPage;
        totalItems.value = response.data.totalData;
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

watchEffect(fetchItems);

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value;
});

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const resolveActive = (active, type) => {
  let data = "";

  if (active == 1) data = ["success", "Active"];

  if (active == 0) data = ["secondary", "In Active"];

  if (type == "color") {
    return data[0];
  }

  return data[1];
};

if (localStorage.getItem("deleted") == 1) {
  snackbarText.value = "Deleted Teacher";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("deleted");
}

onMounted(() => {
  window.scrollTo(0, 0);
});

const onSubmit = () => {
  isSubmit.value = false;
  isOverlay.value = true;

  teacherStore
    .addTeacher({ teachername: item.value.name })
    .then((response) => {
      if (response.status === 200) {
        // let { data } = response;
        fetchItems();
        isDialogVisible.value = false;
        isOverlay.value = false;
        snackbarText.value = "Added Teacher";
        snackbarColor.value = "success";
        isSnackbarVisible.value = true;
      } else {
        isOverlay.value = false;
        console.log("error");
      }
    })
    .catch((error) => {
      isOverlay.value = false;
      snackbarText.value = error.response.data.error.message;
      snackbarColor.value = "error";
      isSnackbarVisible.value = true;
    });

  const {
    id,
    prefix,
    firstname,
    surname,
    email,
    tel,
    active,
    signature_file,
    address,
    province_id,
    amphur_id,
    tumbol_id,
  } = { ...item.value };

  let dataSend = {
    id,
    prefix,
    firstname,
    surname,
    email,
    tel,
    active,
    address,
    province_id,
    amphur_id,
    tumbol_id,
  };

  if (signature_file != undefined && signature_file != null) {
    dataSend.signature_file = signature_file[0];
  }

  if (item.value.id == null) {
    teacherStore
      .addTeacher({
        ...item.value,
      })
      .then((response) => {
        if (response.status === 200) {
          isDialogVisible.value = false;
          snackbarText.value = "Added Teacher";
          snackbarColor.value = "success";
          isSnackbarVisible.value = true;
          isOverlay.value = false;
          isSubmit.value = false;
          fetchItems();
        } else {
          isOverlay.value = false;
          isSubmit.value = false;
          console.log(response.data);
        }
      })
      .catch((error) => {
        isOverlay.value = false;
        isSubmit.value = false;
        console.log(error.response.data.error.message);
      });
  } else {
    refForm.value?.validate().then(({ valid }) => {
      if (valid) {
        teacherStore
          .editTeacher(dataSend)
          .then((response) => {
            if (response.status === 200) {
              isDialogEditVisible.value = false;
              snackbarText.value = "Updated Teacher";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;
              fetchItems();
            } else {
              isOverlay.value = false;
              console.log("error");
            }
          })
          .catch((error) => {
            isOverlay.value = false;
            snackbarText.value = error.response.data.error.message;
            snackbarColor.value = "error";
            isSnackbarVisible.value = true;
          });
      } else {
        isOverlay.value = false;
      }
    });
  }
};

const onLoadTeacher = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      teacherStore
        .loadTeacher({
          firstname: item.value.firstname,
          surname: item.value.surname,
        })
        .then((response) => {
          if (response.status === 200) {
            item.value.name =
              response.data.firstname + " " + response.data.surname;

            item.value.id = null;

            isSubmit.value = true;
            isOverlay.value = false;
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          item.value.name = "";
          console.log(error.response);
          isOverlay.value = false;
          isSubmit.value = false;
          snackbarText.value = error.response.data.message;
          snackbarColor.value = "error";
          isSnackbarVisible.value = true;
        });
    } else {
      isOverlay.value = false;
    }
  });
};

const onSync = () => {
  isOverlay.value = true;

  teacherStore
    .syncTeacher()
    .then((response) => {
      if (response.status == 200) {
        fetchItems();
        isOverlay.value = false;
        snackbarText.value = "Sync Teacher";
        snackbarColor.value = "success";
        isSnackbarVisible.value = true;
      } else {
        isOverlay.value = false;
        errorToast(response.data);
      }
    })
    .catch((error) => {
      console.log(error.response);
      isOverlay.value = false;
      snackbarText.value = error.response.data.message;
      snackbarColor.value = "error";
      isSnackbarVisible.value = true;
    });
};
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12" class="mb-2 text-right">
        <!-- <VBtn
          color="primary"
          @click="
            () => {
              item = {
                active: 1,
              };
              isDialogVisible = true;
            }
          "
        >
          ADD Teacher</VBtn
        > -->
        <VBtn color="primary" @click="onSync()"> Sync Teacher</VBtn>
      </VCol>
    </VRow>

    <!-- Table -->
    <VCard title="จัดการข้อมูลอาจารย์">
      <VCardItem>
        <VRow class="mt-1 mb-1">
          <!-- Search -->
          <VCol cols="12" sm="4">
            <VSelect
              label="จำนวนรายการ/page"
              v-model="rowPerPage"
              density="compact"
              variant="outlined"
              :items="selectOptions.perPage"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4">
            <VSelect
              :items="selectOptions.departments"
              v-model="advancedSearch.department_id"
              label="หน่วยงาน"
              variant="outlined"
              density="compact"
              clearable
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.fullname"
              label="ชื่อ-นามสกุล"
              density="compact"
            />
          </VCol>
          <VSpacer />

          <VCol cols="12" sm="4">
            <VSelect
              label="Active"
              density="compact"
              variant="outlined"
              :items="selectOptions.actives"
              v-model="advancedSearch.active"
              clearable
            />
          </VCol>

          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <th scope="col" class="font-weight-bold">ชื่อ-นามสกุล</th>
                  <th scope="col" class="font-weight-bold">หน่วยงาน</th>
                  <th scope="col" class="text-center font-weight-bold">
                    สถานะ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    สถานะการเข้าใช้งาน
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    จัดการ
                  </th>
                </tr>
              </thead>
              <!-- 👉 table body -->
              <tbody>
                <tr v-for="it in items" :key="it.id" style="height: 3.75rem">
                  <!-- 👉 User -->
                  <td>
                    <span>
                      {{ it.prefix + it.firstname + " " + it.surname }}
                    </span>
                  </td>
                  <td>
                    <span>
                      {{ it.department_name }}
                    </span>
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    <VChip :color="resolveActive(it.active, 'color')">{{
                      resolveActive(it.active, "text")
                    }}</VChip>
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    <VChip color="primary" v-if="it.user_id">เข้าใช้งาน</VChip>
                    <VChip color="default" v-else>ยังไม่เข้าใช้งาน</VChip>
                  </td>
                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
                    <VBtn
                      color="info"
                      :to="{
                        name: 'cwie-settings-teacher-view-id',
                        params: { id: it.id },
                      }"
                    >
                      View</VBtn
                    >
                  </td>
                </tr>
              </tbody>

              <!-- 👉 table footer  -->
              <tfoot v-show="!items.length">
                <tr>
                  <td colspan="7" class="text-center">No data available</td>
                </tr>
              </tfoot>
              <tfoot v-show="items.length"></tfoot>
            </VTable>
          </VCol>

          <VCol cols="12" sm="12">
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
        <VBtn color="error" @click="isSnackbarVisible = false"> Close </VBtn>
      </template>
    </VSnackbar>

    <VOverlay v-model="isOverlay" contained class="align-center justify-center">
      <VProgressCircular indeterminate />
    </VOverlay>

    <!-- Add Form Dialog -->
    <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" absolute />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มเพิ่มอาจารย์">
        <VCardItem>
          <VForm ref="refForm" v-model="isFormValid">
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="firstname"
                  v-model="item.firstname"
                  label="ชื่อ: "
                  placeholder="Firstname"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  id="surname"
                  v-model="item.surname"
                  label="นามสกุล: "
                  placeholder="Surname"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VBtn color="success" @click="onLoadTeacher">Load Data</VBtn>
              </VCol>

              <VCol cols="12" v-if="item.name">
                <VRow>
                  <VCol cols="6" sm="4" class="font-weight-bold"
                    >ชื่อ-นามสกุล :
                  </VCol>
                  <VCol cols="6" sm="8">{{ item.name }}</VCol>
                  <VCol cols="12">
                    <hr
                      style="border-top: none; border-bottom: 0.5px dotted #ddd"
                    />
                  </VCol>
                </VRow>
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
            Close
          </VBtn>
          <VBtn
            type="submit"
            @click="onSubmit"
            id="btn-submit"
            :disabled="!isSubmit"
          >
            <span>Save</span></VBtn
          >
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
