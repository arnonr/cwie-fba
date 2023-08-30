<script setup>
import { useProvinceStore } from "./useProvinceStore";

const provinceStore = useProvinceStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const item = ref({});
const isOverlay = ref(true);
const orderBy = ref("name_th");
const order = ref("asc");

const refForm = ref();
const isFormValid = ref(false);
const isSubmit = ref(false);

const isDialogEditVisible = ref(false);

const advancedSearch = reactive({
  name_th: "",
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
});

// 👉 Fetching
const fetchItems = () => {
  let search = {
    ...advancedSearch,
  };

  provinceStore
    .fetchProvinces({
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

onMounted(() => {
  window.scrollTo(0, 0);
});

const onSubmit = () => {
  isOverlay.value = true;

  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      provinceStore
        .editProvince({
          ...item.value,
        })
        .then((response) => {
          if (response.status === 200) {
            isDialogEditVisible.value = false;
            snackbarText.value = "Updated Province";
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
    isOverlay.value = false;
  });
};
</script>

<template>
  <div>
    <!-- Table -->
    <VCard title="จัดการค่านิเทศ/ค่าเดินทาง">
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
            <VTextField
              v-model="advancedSearch.name_th"
              label="ชื่อจังหวัด/Name"
              density="compact"
            />
          </VCol>
          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <th scope="col" class="font-weight-bold">จังหวัด</th>
                  <th scope="col" class="text-center font-weight-bold">
                    ค่านิเทศ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    ค่าเดินทาง
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    จัดการ
                  </th>
                </tr>
              </thead>
              <!-- 👉 table body -->
              <tbody>
                <tr
                  v-for="it in items"
                  :key="it.province_id"
                  style="height: 3.75rem"
                >
                  <!-- 👉 User -->
                  <td>
                    <span>
                      {{ it.name_th }}
                    </span>
                  </td>
                  <td class="text-center" style="min-width: 110px">
                    {{ it.visit_expense }}
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    {{ it.travel_expense }}
                  </td>
                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
                    <!-- <VBtn
                    color="primary"
                    @click="
                      () => {
                        isDialogViewVisible = true;
                        item = it;
                      }
                    "
                  >
                    View</VBtn
                  > -->
                    <VBtn
                      color="success"
                      class="ml-1"
                      @click="
                        () => {
                          isDialogEditVisible = true;
                          item = { ...it };
                        }
                      "
                    >
                      Edit</VBtn
                    >
                    <!-- <VBtn
                      icon
                      size="x-small"
                      color="success"
                      class="ml-1"
                      :to="{
                        name: 'province-edit-id',
                        params: { id: it.province_id },
                      }"
                    >
                      <VIcon size="22" icon="tabler-edit" />
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

    <VOverlay
      v-model="isOverlay"
      contained
      persistent
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
    <!-- </section> -->

    <!-- View Dialog -->
    <!-- <VDialog v-model="isDialogViewVisible" persistent class="v-dialog-sm">
    <DialogCloseBtn @click="isDialogViewVisible = !isDialogViewVisible" />
    <VCard title="ข้อมูลค่านิเทศ/ค่าเดินทาง">
      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VRow>
          <VCol cols="4" class="font-weight-bold">ชื่อจังหวัด : </VCol>
          <VCol cols="8">{{ item.name_th }}</VCol>
          <VCol cols="12">
            <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
          </VCol>
          <VCol cols="4" class="font-weight-bold">ค่านิเทศ (บาท) : </VCol>
          <VCol cols="8">{{ item.visit_expense }}</VCol>
          <VCol cols="12">
            <hr style="border-top: none; border-bottom: 0.5px dotted #ddd" />
          </VCol>
          <VCol cols="4" class="font-weight-bold">ค่าเดินทาง (บาท) : </VCol>
          <VCol cols="8">{{ item.travel_expense }}</VCol>
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
  </VDialog> -->

    <!-- Form Dialog -->
    <VDialog v-model="isDialogEditVisible" persistent class="v-dialog-sm">
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="isDialogEditVisible = !isDialogEditVisible" />

      <!-- Dialog Content -->
      <VCard title="แบบฟอร์ม ค่านิเทศ/ค่าเดินทาง">
        <VCardItem>
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="item.name_th"
                  label="ชื่อจังหวัด: "
                  placeholder="Provinve Name"
                  disabled
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  id="title"
                  v-model="item.visit_expense"
                  placeholder=""
                  label="ค่านิเทศ (บาท)"
                  type="number"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  id="title"
                  v-model="item.travel_expense"
                  placeholder=""
                  label="ค่าเดินทาง (บาท)"
                  type="number"
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
          <VBtn type="submit" @click="onSubmit"> Save </VBtn>
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
