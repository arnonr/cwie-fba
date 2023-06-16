<script setup>
import { useCompanyStore } from "./useCompanyStore";

const companyStore = useCompanyStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const isOverlay = ref(true);
const orderBy = ref("name_th");
const order = ref("asc");

const advancedSearch = reactive({
  name_th: "",
  blacklist: 0,
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
  provinces: [],
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  blacklists: [
    { title: "None", value: 0 },
    { title: "Blacklist", value: 1 },
  ],
});

const fetchProvinces = () => {
  companyStore
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

// 👉 Fetching
const fetchItems = () => {
  let search = {
    ...advancedSearch,
    includeAll: true,
  };

  companyStore
    .fetchCompanies({
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

const resolveBlacklist = (b, type) => {
  let data = "";

  if (b == 1) data = ["error", "Blacklist"];

  if (b == 0) data = ["secondary", "None"];

  if (type == "color") {
    return data[0];
  }

  return data[1];
};

if (localStorage.getItem("deleted") == 1) {
  snackbarText.value = "Deleted Company";
  snackbarColor.value = "success";
  isSnackbarVisible.value = true;
  localStorage.removeItem("deleted");
}

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12" class="mb-2 text-right">
        <VBtn
          color="primary"
          :to="{
            name: 'cwie-settings-company-add',
          }"
        >
          ADD COMPANY</VBtn
        >
      </VCol>
    </VRow>

    <!-- Table -->
    <VCard title="จัดการสถานประกอบการ">
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
              label="จังหวัด/Province"
              v-model="advancedSearch.province_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.provinces"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4">
            <VTextField
              v-model="advancedSearch.name_th"
              label="ชื่อสถานประกอบการ/Name"
              density="compact"
            />
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4">
            <VSelect
              label="Blacklist"
              density="compact"
              variant="outlined"
              :items="selectOptions.blacklists"
              v-model="advancedSearch.blacklist"
              clearable
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VSelect
              label="Status"
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
                  <th scope="col" class="font-weight-bold">
                    ชื่อสถานประกอบการ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    จังหวัด
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    อำเภอ/เขต
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    Blacklist
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
                      {{ it.name_th }}
                    </span>
                  </td>
                  <td class="text-center" style="min-width: 110px">
                    {{ it.province_name }}
                  </td>
                  <td class="text-center" style="min-width: 100px">
                    {{ it.amphur_name }}
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <VChip :color="resolveBlacklist(it.blacklist, 'color')">{{
                      resolveBlacklist(it.blacklist, "text")
                    }}</VChip>
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
                      :to="{
                        name: 'cwie-settings-company-view-id',
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
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
