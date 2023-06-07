<script setup>
import { requiredValidator } from "@validators";
import { useTeacherStore } from "./useTeacherStore";

const teacherStore = useTeacherStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const item = ref({});
const isOverlay = ref(true);
const orderBy = ref("id");
const order = ref("desc");

const refForm = ref();
const refAddForm = ref();
const isFormValid = ref(false);
const isAddFormValid = ref(false);
const isSubmit = ref(false);

const isDialogViewVisible = ref(false);
const isDialogAddVisible = ref(false);
const isDialogEditVisible = ref(false);
const isDialogDelVisible = ref(false);

const advancedSearch = reactive({
  fullname: "",
  department_id: "",
});

const resetAdvancedSearch = () => {
  advancedSearch.fullname = "";
  department_id = "";
};

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
  departments: [],
  provinces: [],
  amphurs: [],
  tumbols: [],
});

// 👉 Fetching

const fetchDepartments = () => {
  teacherStore
    .fetchDepartments({
      faculty_id: 1,
      includeAll: true,
    })
    .then((response) => {
      if (response.status === 200) {
        selectOptions.value.departments = response.data.data.map((r) => {
          return { title: r.name_th, value: r.department_id };
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

const fetchProvinces = () => {
  teacherStore
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

const fetchAmphurs = () => {
  teacherStore
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
  teacherStore
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

const fetchItems = () => {
  // isOverlay.value = true;
  let search = {
    ...advancedSearch,
    includeAll: true,
  };

  teacherStore
    .fetchTeachers({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      ...search,
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
  }
);

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

onMounted(() => {
  window.scrollTo(0, 0);
});

const onSubmit = () => {
  isOverlay.value = true;

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
          isDialogAddVisible.value = false;
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
  isSubmit.value = true;
  isOverlay.value = true;
  refAddForm.value?.validate().then(({ valid }) => {
    if (valid) {
      teacherStore
        .loadTeacher({
          username: item.value.username,
        })
        .then((response) => {
          if (response.status === 200) {
            item.value = response.data;
            item.value.account_type_name = resolveAccountType(
              item.value.account_type
            );
            item.value.user_id = null;

            isOverlay.value = false;
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          isOverlay.value = false;
          isSubmit.value = false;
          snackbarText.value = error.response.data.error.message;
          snackbarColor.value = "error";
          isSnackbarVisible.value = true;
        });
    } else {
      isOverlay.value = false;
    }
  });
};

const onDelete = (id) => {
  isOverlay.value = true;
  teacherStore
    .deleteTeacher({
      id: id,
    })
    .then((response) => {
      if (response.status === 200) {
        isDialogDelVisible.value = false;
        snackbarText.value = "Deleted Teacher";
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
    <VRow>
      <VCol cols="12" class="mb-2 text-right">
        <VBtn
          color="primary"
          @click="
            () => {
              item = {
                active: 1,
              };
              isDialogAddVisible = true;
            }
          "
        >
          ADD Teacher</VBtn
        >
      </VCol>
    </VRow>

    <VCard title="จัดการข้อมูลอาจารย์">
      <VCardItem>
        <VRow class="mt-1 mb-1">
          <!-- Search -->
          <VCol cols="12" sm="4">
            <AppSelect
              label="จำนวนรายการ/page"
              v-model="rowPerPage"
              density="compact"
              variant="outlined"
              :items="selectOptions.perPage"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4">
            <AppSelect
              :items="selectOptions.departments"
              v-model="advancedSearch.department_id"
              label="หน่วยงาน"
              variant="outlined"
              density="compact"
              clearable
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.fullname"
              label="ชื่อ-นามสกุล"
              density="compact"
            />
          </VCol>

          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <th scope="col" class="font-weight-bold">ชื่อ-นามสกุล</th>
                  <th scope="col" class="text-center font-weight-bold">
                    หน่วยงาน
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    สถานะ
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
                      {{ it.firstname }}
                    </span>
                  </td>
                  <td class="text-center" style="min-width: 110px">
                    {{ it.department_name }}
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    <VChip :color="resolveActive(it.active, 'color')">{{
                      resolveActive(it.active, "text")
                    }}</VChip>
                  </td>
                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
                    <VBtn
                      color="info"
                      @click="
                        () => {
                          item = { ...it };
                          isDialogViewVisible = true;
                        }
                      "
                    >
                      View</VBtn
                    >
                    <VBtn
                      color="success"
                      class="ml-1"
                      @click="
                        () => {
                          //
                          item = { ...it };

                          item.signature_file_old = null;

                          if (item.signature_file != null) {
                            item.signature_file_old = item.signature_file;
                          }
                          item.signature_file = [];
                          isDialogEditVisible = true;
                        }
                      "
                    >
                      Edit</VBtn
                    >
                    <VBtn size="38" class="ml-1" color="error">
                      <VIcon
                        icon="tabler-trash"
                        size="22"
                        @click="
                          () => {
                            item = { ...it };
                            item.signature_file = [];
                            isDialogDelVisible = true;
                          }
                        "
                      />
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

    <VOverlay v-model="isOverlay" class="align-center justify-center">
      <VProgressCircular indeterminate />
    </VOverlay>
    <!-- </section> -->

    <!-- View Dialog -->
    <VDialog v-model="isDialogViewVisible" class="v-dialog-sm" width="auto">
      <DialogCloseBtn @click="isDialogViewVisible = !isDialogViewVisible" />
      <VCard title="ข้อมูลอาจารย์">
        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VRow>
            <VCol cols="6" sm="4" class="font-weight-bold"
              >ชื่อ-นามสกุล :
            </VCol>
            <VCol cols="6" sm="8">{{
              item.prefix + item.firstname + item.surname
            }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>

            <VCol cols="6" sm="2" class="font-weight-bold">โทรศัพท์ : </VCol>
            <VCol cols="6" sm="4">{{ item.tel }}</VCol>
            <!-- <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol> -->
            <VCol cols="6" sm="2" class="font-weight-bold">เมล : </VCol>
            <VCol cols="6" sm="4">{{ item.email }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>

            <VCol cols="6" sm="4" class="font-weight-bold">หน่วยงาน : </VCol>
            <VCol cols="6" sm="8">{{
              item.department ? item.department.name_th : null
            }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>

            <VCol cols="6" sm="4" class="font-weight-bold">ที่อยู่ : </VCol>
            <VCol cols="6" sm="8">
              {{ item.address }}
            </VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>

            <VCol cols="6" sm="2" class="font-weight-bold">จังหวัด : </VCol>
            <VCol cols="6" sm="4">
              {{ item.province ? item.province.name_th : "" }}
            </VCol>

            <VCol cols="6" sm="2" class="font-weight-bold">อำเภอ : </VCol>
            <VCol cols="6" sm="4">
              {{ item.amphur ? item.amphur.name_th : "" }}
            </VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>

            <VCol cols="6" sm="2" class="font-weight-bold">ตำบล : </VCol>
            <VCol cols="6" sm="4">
              {{ item.tumbol ? item.amphur.tumbol : "" }}
            </VCol>

            <VCol cols="6" sm="2" class="font-weight-bold">สถานะ : </VCol>
            <VCol cols="6" sm="4"
              ><VChip :color="resolveActive(item.active, 'color')">{{
                resolveActive(item.active, "text")
              }}</VChip></VCol
            >
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>

            <VCol cols="6" sm="4" class="font-weight-bold">ลายเซ็นต์ : </VCol>
            <VCol cols="6" sm="8">
              <VImg :src="item.signature_file" alt="" />
            </VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDialogViewVisible = false"
          >
            Cancel
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Add Form Dialog -->
    <VDialog v-model="isDialogAddVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogAddVisible = !isDialogAddVisible"
        absolute
      />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์มเพิ่มผู้ใช้งาน">
        <VCardItem>
          <VForm ref="refAddForm" v-model="isAddFormValid">
            <!-- @submit.prevent="onAddSubmit" -->
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="username"
                  v-model="item.username"
                  label="ICIT Account: "
                  placeholder="Username"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VBtn color="success" @click="onLoadTeacher">Load Data</VBtn>
              </VCol>

              <VCol cols="12" v-if="item.name != ''">
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
                  <!-- <VCol cols="6" sm="4" class="font-weight-bold"
                    >บัญชีเข้าใช้งาน :
                  </VCol>
                  <VCol cols="6" sm="8">{{ item.username }}</VCol>
                  <VCol cols="12">
                    <hr
                      style="border-top: none; border-bottom: 0.5px dotted #ddd"
                    />
                  </VCol> -->
                  <VCol cols="6" sm="4" class="font-weight-bold">เมล : </VCol>
                  <VCol cols="6" sm="8">{{ item.email }}</VCol>
                  <VCol cols="12">
                    <hr
                      style="border-top: none; border-bottom: 0.5px dotted #ddd"
                    />
                  </VCol>
                  <VCol cols="6" sm="4" class="font-weight-bold"
                    >ประเภทบัญชี :
                  </VCol>
                  <VCol cols="6" sm="8">{{
                    resolveAccountType(item.account_type)
                  }}</VCol>
                  <VCol cols="12">
                    <hr
                      style="border-top: none; border-bottom: 0.5px dotted #ddd"
                    />
                  </VCol>
                </VRow>
              </VCol>

              <!-- <VCol cols="12">
                <AppSelect
                  :items="selectOptions.accountTypes"
                  v-model="item.account_type"
                  label="ประเภทผู้ใช้งาน"
                  variant="outlined"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  :items="[
                    { title: 'In Active', value: 0 },
                    { title: 'Active', value: 1 },
                  ]"
                  v-model="item.active"
                  label="สถานะ"
                  variant="outlined"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  :items="[
                    { title: 'Unblock', value: 0 },
                    { title: 'Blocked', value: 1 },
                  ]"
                  v-model="item.blocked"
                  label="ระงับการใช้งาน"
                  variant="outlined"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  id="txt-email"
                  v-model="item.email"
                  placeholder="Email"
                  label="เมล"
                />
              </VCol> -->
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogEditVisible = false"
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

    <!-- Edit Form Dialog -->
    <VDialog v-model="isDialogEditVisible" class="v-dialog-sm" min-width="70%">
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogEditVisible = !isDialogEditVisible"
        absolute
      />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์ม ข้อมูลอาจารย์">
        <VCardItem>
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12" md="2">
                <AppTextField
                  id="txt-prefix"
                  v-model="item.prefix"
                  label="คำนำหน้า: "
                  placeholder="Prefix"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="5">
                <AppTextField
                  id="txt-firstname"
                  v-model="item.firstname"
                  label="ชื่อ: "
                  placeholder="Firstname"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="5">
                <AppTextField
                  id="txt-surname"
                  v-model="item.surname"
                  label="นามสกุล: "
                  placeholder="Surname"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppTextField
                  id="txt-department"
                  v-model="item.department.name_th"
                  label="สาขาวิชา: "
                  disabled
                  placeholder="Department"
                />
              </VCol>

              <VCol cols="12" md="3">
                <AppTextField
                  id="txt-email"
                  v-model="item.email"
                  placeholder="Email"
                  label="เมล"
                />
              </VCol>

              <VCol cols="12" md="3">
                <AppTextField
                  id="txt-tel"
                  v-model="item.tel"
                  placeholder="Tel"
                  label="โทรศัพท์"
                />
              </VCol>

              <VCol cols="12" md="2">
                <AppSelect
                  :items="[
                    { title: 'In Active', value: 0 },
                    { title: 'Active', value: 1 },
                  ]"
                  v-model="item.active"
                  label="สถานะ"
                  variant="outlined"
                />
              </VCol>

              <VCol cols="12" md="10">
                <VFileInput
                  label="Upload Signature (.png)"
                  id="signature_file"
                  v-model="item.signature_file"
                  persistent-placeholder
                />
              </VCol>

              <VCol cols="12" md="2" class="pl-2">
                <a
                  :href="
                    item.signature_file_old != null
                      ? item.signature_file_old
                      : '/'
                  "
                  target="_blank"
                >
                  <VBtn style="width: 100%" color="warning"> Old File </VBtn></a
                >
              </VCol>

              <VCol cols="12">
                <hr />
              </VCol>

              <VCol>
                <h3>ที่อยู่ตามบัตรประชาชน</h3>
              </VCol>

              <VCol cols="12" sm="12">
                <AppTextField
                  id="txt-address"
                  v-model="item.address"
                  placeholder="Address"
                  label="บ้านเลขที่"
                />
              </VCol>

              <VCol cols="12" sm="4">
                <AppSelect
                  :items="selectOptions.provinces"
                  v-model="item.province_id"
                  label="จังหวัด"
                  variant="outlined"
                  clearable
                />
              </VCol>

              <VCol cols="12" sm="4">
                <AppSelect
                  :items="selectOptions.amphurs"
                  v-model="item.amphur_id"
                  label="อำเภอ/เขต"
                  variant="outlined"
                  clearable
                />
              </VCol>

              <VCol cols="12" sm="4">
                <AppSelect
                  :items="selectOptions.tumbols"
                  v-model="item.tumbol_id"
                  label="ตำบล/แขวง"
                  variant="outlined"
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
            @click="isDialogEditVisible = false"
          >
            Close
          </VBtn>
          <VBtn type="submit" @click="onSubmit" id="btn-submit">
            <span>Save</span></VBtn
          >
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
          <VBtn @click="onDelete(item.user_id)" color="error"> Delete </VBtn>
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
