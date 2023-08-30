<script setup>
import { requiredValidator } from "@validators";
import { useMajorHeadStore } from "./useMajorHeadStore";

const majorHeadStore = useMajorHeadStore();
const route = useRoute();
const router = useRouter();

const props = defineProps(["semester_year", "semester_id"]);
const semester_year = ref(props.semester_year);
const semester_id = ref(props.semester_id);

watchEffect(() => {
  semester_year.value = props.semester_year;
});

watchEffect(() => {
  semester_id.value = props.semester_id;
});

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const item = ref({});
const isOverlay = ref(true);
const orderBy = ref("id");
const order = ref("asc");

const refForm = ref();
const isFormValid = ref(false);

const isDialogVisible = ref(false);
const isDialogDelVisible = ref(false);

const selectOptions = ref({
  perPage: [{ title: "20", value: 20 }],
  orderBy: [{ title: "ลำดับ/level", value: "level" }],
  order: [
    { title: "น้อย -> มาก", value: "asc" },
    { title: "มาก -> น้อย", value: "desc" },
  ],
  departments: [],
});

const fetchMajors = () => {
  majorHeadStore
    .fetchMajors({})
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.majors = response.data.data.map((r) => {
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
fetchMajors();

const fetchTeachers = () => {
  majorHeadStore
    .fetchTeachers({})
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

// 👉 Fetching
const fetchItems = () => {
  majorHeadStore
    .fetchMajorHeads({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      semester_id: route.params.id,
      includeAll: true,
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

onMounted(() => {
  window.scrollTo(0, 0);
});

const onSubmit = () => {
  isOverlay.value = true;

  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      if (item.value.id != null) {
        majorHeadStore
          .editMajorHead({
            ...item.value,
            semester_id: semester_id.value,
          })
          .then((response) => {
            if (response.status === 200) {
              isDialogVisible.value = false;
              snackbarText.value = "Updated Major Head";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;

              fetchItems();
            } else {
              isOverlay.value = false;
              console.log("error");
            }
          })
          .catch((error) => {
            console.error(error);
          });
      } else {
        majorHeadStore
          .addMajorHead({
            ...item.value,
            semester_id: semester_id.value,
          })
          .then((response) => {
            if (response.status === 200) {
              isDialogVisible.value = false;
              snackbarText.value = "Added Major Head";
              snackbarColor.value = "success";
              isSnackbarVisible.value = true;

              fetchItems();
            } else {
              isOverlay.value = false;
              console.log("error");
            }
          })
          .catch((error) => {
            console.error(error);
          });
      }
    }
    isOverlay.value = false;
  });
};

const onDelete = (id) => {
  isOverlay.value = true;
  majorHeadStore
    .deleteMajorHead({
      id: id,
    })
    .then((response) => {
      if (response.status === 200) {
        isDialogDelVisible.value = false;
        snackbarText.value = "Deleted Major Head";
        snackbarColor.value = "success";
        isSnackbarVisible.value = true;
        fetchItems();
        isOverlay.value = false;
      } else {
        console.log("error");
        isOverlay.value = false;
      }
    })
    .catch((error) => {
      isOverlay.value = false;
      snackbarText.value = error.response.data.error.message;
      snackbarColor.value = "error";
      isSnackbarVisible.value = true;
    });
};
</script>

<template>
  <div>
    <!-- Table -->
    <VRow>
      <VCol cols="12" class="mb-2 text-right">
        <VBtn
          color="primary"
          @click="
            () => {
              item.id = null;
              item.major_id = null;
              item.teacher_id = null;
              isDialogVisible = true;
            }
          "
        >
          ADD Teacher</VBtn
        >
      </VCol>
    </VRow>
    <VCard>
      <VCardTitle class="ma-2 pt-6">
        ประธานอาจารย์นิเทศ ปีการศึกษา {{ semester_year }}
      </VCardTitle>
      <VCardItem>
        <VRow class="mt-1 mb-1">
          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <th scope="col" class="font-weight-bold">สาขาวิชา</th>
                  <th scope="col" class="font-weight-bold">
                    ประธานอาจารย์นิเทศ
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
                      {{ it.major_name }}
                    </span>
                  </td>
                  <td>
                    {{ it.teacher_name }}
                  </td>
                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
                    <VBtn
                      color="success"
                      class="ml-1"
                      @click="
                        () => {
                          isDialogVisible = true;
                          item = { ...it };
                        }
                      "
                    >
                      Edit</VBtn
                    >

                    <VBtn
                      color="error"
                      class="ml-1"
                      size="38"
                      @click="
                        () => {
                          isDialogDelVisible = true;
                          item = { ...it };
                        }
                      "
                    >
                      <VIcon icon="tabler-trash" size="22" />
                    </VBtn>
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

    <VOverlay
      v-model="isOverlay"
      contained
      persistent
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
    <!-- </section> -->

    <!-- Form Dialog -->
    <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์ม เลือกประธานอาจารย์นิเทศ">
        <VCardItem>
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="semester_year"
                  :value="semester_year"
                  disabled
                  placeholder=""
                  label="ปีการศึกษา"
                />
              </VCol>

              <VCol cols="12" md="12">
                <label
                  class="v-label font-weight-bold"
                  for="major_id"
                  cols="12"
                  md="12"
                  >สาขาวิชา :
                </label>
                <AppSelect
                  :items="selectOptions.majors"
                  v-model="item.major_id"
                  :rules="[requiredValidator]"
                  variant="outlined"
                  placeholder="Major"
                />
              </VCol>

              <VCol cols="12" md="12">
                <label
                  class="v-label font-weight-bold"
                  for="teacher_id"
                  cols="12"
                  md="12"
                  >ประธานอาจารย์นิเทศ :
                </label>
                <AppSelect
                  :items="selectOptions.teachers"
                  v-model="item.teacher_id"
                  :rules="[requiredValidator]"
                  variant="outlined"
                  placeholder="Teacher"
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
            Close
          </VBtn>
          <VBtn type="submit" @click="onSubmit"> Save </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Del Dialog -->
    <VDialog v-model="isDialogDelVisible" class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogDelVisible = !isDialogDelVisible" />

      <!-- Dialog Content -->
      <VCard title="ยืนยันการลบข้อมูล">
        <VCardText> คุณต้องการลบข้อมูลใช่หรือไม่ </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDialogDelVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn @click="onDelete(item.id)" color="error"> Delete </VBtn>
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
