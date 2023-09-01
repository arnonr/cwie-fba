<script setup>
import { requiredValidator } from "@validators";
import dayjs from "dayjs";
import { useUserStore } from "./useUserStore";

const userStore = useUserStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const item = ref({});
const isOverlay = ref(true);
const orderBy = ref("name");
const order = ref("asc");

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
  name: "",
  account_type: "",
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
  accountTypes: [
    { title: "นักศึกษา", value: 1 },
    { title: "อาจารย์", value: 2 },
    { title: "เจ้าหน้าที่", value: 3 },
    { title: "Admin", value: 4 },
  ],
});

// 👉 Fetching
const fetchItems = () => {
  // isOverlay.value = true;
  let search = {
    ...advancedSearch,
  };

  userStore
    .fetchUsers({
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

const resolveAccountType = (account_type) => {
  if (account_type === 1) return "นักศึกษา";
  if (account_type === 2) return "อาจารย์";
  if (account_type === 3) return "เจ้าหน้าที่";
  if (account_type === 4) return "Admin";
  return "";
};

const resolveActive = (active, blocked_at, type) => {
  let data = "";
  if (blocked_at !== null) {
    data = ["error", "Blocked"];
  } else {
    if (active == 1) data = ["success", "Active"];

    if (active == 0) data = ["secondary", "In Active"];
  }

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

  if (item.value.id == null) {
    userStore
      .addUser({
        username: item.value.username,
      })
      .then((response) => {
        if (response.status === 200) {
          isDialogAddVisible.value = false;
          snackbarText.value = "Added User";
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
        let blocked = null;
        if (item.value.blocked == 1) {
          if (item.value.blocked_at !== null) {
            blocked = item.value.blocked_at;
          } else {
            blocked = dayjs().format("YYYY-MM-DD");
          }
        }

        userStore
          .editUser({
            ...item.value,
            blocked: undefined,
            blocked_at: blocked,
          })
          .then((response) => {
            if (response.status === 200) {
              isDialogEditVisible.value = false;
              snackbarText.value = "Updated User";
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

const onLoadUser = () => {
  isSubmit.value = true;
  isOverlay.value = true;
  refAddForm.value?.validate().then(({ valid }) => {
    if (valid) {
      userStore
        .loadUser({
          username: item.value.username,
        })
        .then((response) => {
          if (response.status === 200) {
            item.value = response.data;
            item.value.account_type_name = resolveAccountType(
              item.value.account_type
            );
            item.value.id = null;

            isOverlay.value = false;
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((e) => {
          isOverlay.value = false;
          isSubmit.value = false;
          snackbarText.value = e.response.data.message;
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
  userStore
    .deleteUser({
      id: id,
    })
    .then((response) => {
      if (response.status === 200) {
        isDialogDelVisible.value = false;
        snackbarText.value = "Deleted User";
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
  <!-- Table -->
  <div>
    <VRow>
      <VCol cols="12" class="mb-2 text-right">
        <VBtn
          color="primary"
          @click="
            () => {
              item = {
                active: 1,
                blocked_at: null,
                account_type: 3,
              };
              isDialogAddVisible = true;
            }
          "
        >
          ADD STAFF</VBtn
        >
      </VCol>
    </VRow>

    <VCard title="จัดการผู้ใช้งาน">
      <VCardItem>
        <VRow class="mt-1 mb-1">
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
              :items="selectOptions.accountTypes"
              v-model="advancedSearch.account_type"
              label="ประเภทผู้ใช้งาน"
              variant="outlined"
              density="compact"
              clearable
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.name"
              label="ชื่อผู้ใช้งาน/Name"
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
                    ICIT Account
                  </th>
                  <th scope="col" class="font-weight-bold">เมล</th>
                  <th scope="col" class="text-center font-weight-bold">
                    ประเภทบัญชี
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
                      {{ it.name }}
                    </span>
                  </td>
                  <td class="text-center" style="min-width: 110px">
                    {{ it.username }}
                  </td>
                  <td>
                    {{ it.email }}
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    {{ resolveAccountType(it.account_type) }}
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    <VChip
                      :color="resolveActive(it.active, it.blocked_at, 'color')"
                      >{{
                        resolveActive(it.active, it.blocked_at, "text")
                      }}</VChip
                    >
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
                          item = { ...it };

                          if (it.blocked_at !== null) {
                            item.blocked = 1;
                          } else {
                            item.blocked = 0;
                          }
                          isDialogEditVisible = true;
                        }
                      "
                    >
                      Edit</VBtn
                    >
                    <!-- <VBtn size="38" class="ml-1" color="error">
                      <VIcon
                        icon="tabler-trash"
                        size="22"
                        @click="
                          () => {
                            item = { ...it };
                            isDialogDelVisible = true;
                          }
                        "
                      />
                    </VBtn> -->
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
    <!-- </section> -->

    <!-- View Dialog -->
    <VDialog v-model="isDialogViewVisible" class="v-dialog-sm">
      <DialogCloseBtn @click="isDialogViewVisible = !isDialogViewVisible" />
      <VCard title="ข้อมูลผู้ใช้งาน">
        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VRow>
            <VCol cols="6" sm="4" class="font-weight-bold"
              >ชื่อ-นามสกุล :
            </VCol>
            <VCol cols="6" sm="8">{{ item.name }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>
            <VCol cols="6" sm="4" class="font-weight-bold"
              >บัญชีเข้าใช้งาน :
            </VCol>
            <VCol cols="6" sm="8">{{ item.username }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>
            <VCol cols="6" sm="4" class="font-weight-bold">เมล : </VCol>
            <VCol cols="6" sm="8">{{ item.email }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>
            <VCol cols="6" sm="4" class="font-weight-bold">ประเภทบัญชี : </VCol>
            <VCol cols="6" sm="8">{{
              resolveAccountType(item.account_type)
            }}</VCol>
            <VCol cols="12">
              <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
            </VCol>
            <VCol cols="6" sm="4" class="font-weight-bold">สถานะ : </VCol>
            <VCol cols="6" sm="8"
              ><VChip
                :color="resolveActive(item.active, item.blocked_at, 'color')"
                >{{
                  resolveActive(item.active, item.blocked_at, "text")
                }}</VChip
              ></VCol
            >
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
            Close
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Add Form Dialog -->
    <VDialog
      v-model="isDialogAddVisible"
      contained
      persistent
      class="v-dialog-sm"
    >
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
                <VBtn color="success" @click="onLoadUser">Load Data</VBtn>
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
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogEditVisible = false"
          >
            Cancel
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
    <VDialog v-model="isDialogEditVisible" contained class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn
        @click="isDialogEditVisible = !isDialogEditVisible"
        absolute
      />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์ม ผู้ใช้งาน">
        <VCardItem>
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  id="txt-name"
                  v-model="item.name"
                  label="ชื่อ-นามสกุล: "
                  placeholder="Name"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  id="txt-email"
                  v-model="item.email"
                  placeholder="Email"
                  label="เมล"
                />
              </VCol>

              <VCol cols="12">
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
            </VRow>
          </VForm>
        </VCardItem>

        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogEditVisible = false"
          >
            Cancel
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
