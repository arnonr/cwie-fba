<script setup>
import { useDashboardStore } from "../useDashboardStore";
import { useRoute, useRouter } from "vue-router";

const dashboardStore = useDashboardStore();
const route = useRoute();
const router = useRouter();

const item = ref({});

// 👉 Fetching
const fetchItem = () => {
  dashboardStore
    .fetchNew({
      id: route.params.id,
      includeAll: true,
      //   ...search,
    })
    .then((response) => {
      if (response.status === 200) {
        item.value = response.data.data;
      } else {
        console.log("error");
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

fetchItem();
</script>

<template>
  <div>
    <VRow class="match-height">
      <!--     -->

      <VCol cols="12" class="mt-5">
        <VCard class="pa-5">
          <h2>{{ item.news_title }}</h2>
          <hr />
          <div class="mt-5">
            <VImg :src="item.news_file" cover width="500" class="mx-auto" />
            <div class="mt-5 ml-5 mr-5">{{ item.news_detail }}</div>
          </div>
        </VCard>
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
