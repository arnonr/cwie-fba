<script setup>
import { useDashboardStore } from "./useDashboardStore";

const rowPerPage = ref(8);
const currentPage = ref(1);
const totalPage = ref(1);
const totalItems = ref(0);
const orderBy = ref("news.id");
const order = ref("desc");

const rowPerPage1 = ref(10);
const currentPage1 = ref(1);
const totalPage1 = ref(1);
const totalItems1 = ref(0);

const rowPerPage2 = ref(10);
const currentPage2 = ref(1);
const totalPage2 = ref(1);
const totalItems2 = ref(0);

const dashboardStore = useDashboardStore();

const news = ref([]);
const documentDownloads = ref([]);
const manuals = ref([
  {
    manual_file: "https://youtu.be/qL2st5Ls57c",
    manual_url: "https://youtu.be/qL2st5Ls57c",
    name: "การใช้งานระบบสำหรับนักศึกษา",
  },
  {
    manual_file: "https://youtu.be/QFhDqZdy8ms",
    manual_url: "https://youtu.be/QFhDqZdy8ms",
    name: "การอัพโหลดแผนการปฏิบัติงาน",
  },
]);

// 👉 Fetching
const fetchItems = () => {
  dashboardStore
    .fetchNews({
      perPage: rowPerPage.value,
      currentPage: currentPage.value,
      orderBy: orderBy.value,
      order: order.value,
      //   ...search,
    })
    .then((response) => {
      if (response.status === 200) {
        news.value = response.data.data;
        totalPage.value = response.data.totalPage;
        totalItems.value = response.data.totalData;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

watchEffect(fetchItems);

watchEffect(() => {
  if (currentPage.value > totalPage.value) currentPage.value = totalPage.value;
});

// 👉 Fetching
const fetchDocumentDownload = () => {
  dashboardStore
    .fetchDocumentDownloads({
      perPage: rowPerPage1.value,
      currentPage: currentPage1.value,
      //   orderBy: '',
      //   order: order.value,
      //   ...search,
    })
    .then((response) => {
      if (response.status === 200) {
        documentDownloads.value = response.data.data;
        totalPage1.value = response.data.totalPage;
        totalItems1.value = response.data.totalData;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

fetchDocumentDownload();
</script>

<template>
  <div>
    <VRow class="match-height">
      <!--     -->
      <VCol cols="12" class="mt-5">
        <h2>ข่าวประชาสัมพันธ์</h2>
        <hr />
      </VCol>

      <VCol cols="12" lg="12">
        <VRow>
          <!-- Card -->
          <VCol cols="12" sm="6" md="4" v-for="nw in news" :key="nw.id">
            <VCard
              @click="
                $router.push({
                  name: 'dashboards-new-id',
                  params: { id: nw.id },
                })
              "
              class="news-card cursor-pointer"
            >
              <VImg :src="nw.news_file" cover />

              <VCardText style="min-height: 200px !important">
                <h2 style="font-size: 1.25rem; font-weight: 500">
                  {{ nw.news_title }}
                </h2>
              </VCardText>
              <VCardActions>
                <VBtn> อ่านต่อ <VIcon icon="tabler-arrow-right" /> </VBtn>
                <VSpacer />
              </VCardActions>
            </VCard>
          </VCol>
        </VRow>
        <!-- Pagination -->
        <VRow class="mt-5">
          <VCol>
            <span class="text-sm text-disabled">
              Showing {{ currentPage }} to {{ totalPage }} of
              {{ totalItems }} entries
            </span>
          </VCol>
        </VRow>

        <VPagination
          v-model="currentPage"
          size="small"
          :total-visible="5"
          :length="totalPage"
        />
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="6" lg="6">
        <h2>เอกสารดาวน์โหลด</h2>
        <hr class="mb-2" />

        <VRow>
          <!-- Card -->
          <VCol cols="12" sm="12" md="12">
            <VTable>
              <thead>
                <tr>
                  <th class="text-left">ชื่อเอกสาร</th>
                  <th class="text-center" style="width: 200px">ดาวน์โหลด</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(dd, index) in documentDownloads" :key="index">
                  <td>
                    <a :href="dd.document_file" target="_blank">{{
                      dd.document_name
                    }}</a>
                  </td>
                  <td class="text-center">
                    <a :href="dd.document_file" target="_blank">
                      <VIcon
                        size="22"
                        icon="tabler-file"
                        class="mt-1"
                        style="opacity: 1"
                      />
                    </a>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
        </VRow>
        <!-- Pagination -->
        <VRow class="mt-5">
          <VCol>
            <span class="text-sm text-disabled">
              Showing {{ currentPage1 }} to {{ totalPage1 }} of
              {{ totalItems1 }} entries
            </span>
          </VCol>
        </VRow>

        <VPagination
          v-model="currentPage1"
          size="small"
          :total-visible="5"
          :length="totalPage1"
        />
      </VCol>
      <!--  -->
      <VCol cols="6" lg="6">
        <h2>คู่มือการใช้งานระบบ</h2>
        <hr class="mb-2" />

        <VRow>
          <!-- Card -->
          <VCol cols="12" sm="12" md="12">
            <VTable>
              <thead>
                <tr>
                  <th class="text-left">ไฟล์</th>
                  <th class="text-center" style="width: 200px">ดาวน์โหลด</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(mn, index) in manuals" :key="index">
                  <td>
                    <a :href="mn.manual_url" target="_blank">{{ mn.name }}</a>
                  </td>
                  <td class="text-center">
                    <a :href="mn.manual_url" target="_blank">
                      <VIcon
                        size="22"
                        icon="tabler-file"
                        class="mt-1"
                        style="opacity: 1"
                      />
                    </a>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
        </VRow>
        <!-- Pagination -->
        <VRow class="mt-5">
          <VCol>
            <span class="text-sm text-disabled">
              Showing {{ currentPage2 }} to {{ totalPage2 }} of
              {{ totalItems2 }} entries
            </span>
          </VCol>
        </VRow>

        <VPagination
          v-model="currentPage2"
          size="small"
          :total-visible="5"
          :length="totalPage2"
        />
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/libs/apex-chart.scss";
</style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
