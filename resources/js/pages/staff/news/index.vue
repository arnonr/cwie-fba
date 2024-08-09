<script setup>
import { useNewsStore } from "./useNewsStore";

const newsStore = useNewsStore();

const rowPerPage = ref(20);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const items = ref([]);
const isOverlay = ref(true);
const orderBy = ref("name");
const order = ref("asc");

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
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
});

// 👉 Fetching
const fetchItems = () => {
  let search = {
    ...advancedSearch,
    includeAll: true,
  };

  newsStore
    .fetchNewses({
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

const resolvePinned = (pinned, type) => {
  let data = "";

  if (pinned == 1) data = ["success", "Yes"];

  if (pinned == 0) data = ["secondary", "No"];

  if (type == "color") {
    return data[0];
  }

  return data[1];
};

if (localStorage.getItem("deleted") == 1) {
  snackbarText.value = "Deleted News";
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
            name: 'staff-news-add',
          }"
        >
          ADD News</VBtn
        >
      </VCol>
    </VRow>

    <!-- Table -->
    <VCard title="จัดการข่าว">
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
            <!-- <VSelect
              label="จังหวัด/Province"
              v-model="advancedSearch.province_id"
              density="compact"
              variant="outlined"
              clearable
              :items="selectOptions.provinces"
            /> -->
          </VCol>
          <VSpacer />
          <VCol cols="12" sm="4">
            <!--  -->
          </VCol>

          <!-- Table -->
          <VCol cols="12" sm="12">
            <VTable class="text-no-wrap">
              <!-- 👉 table head -->
              <thead>
                <tr>
                  <th scope="col" class="font-weight-bold">หัวข้อข่าว</th>

                  <th scope="col" class="text-center font-weight-bold">
                    สถานะ
                  </th>
                  <th scope="col" class="text-center font-weight-bold">
                    ปักหมุด
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
                      {{ it.news_title }}
                    </span>
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <VChip :color="resolveActive(it.active, 'color')">{{
                      resolveActive(it.active, "text")
                    }}</VChip>
                  </td>

                  <td class="text-center" style="min-width: 100px">
                    <VChip :color="resolvePinned(it.pinned, 'color')">
                      {{ resolvePinned(it.pinned, "text") }}</VChip
                    >
                  </td>

                  <!-- 👉 Actions -->
                  <td class="text-center" style="min-width: 80px">
                    <VBtn
                      color="info"
                      :to="{
                        name: 'staff-news-view-id',
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
