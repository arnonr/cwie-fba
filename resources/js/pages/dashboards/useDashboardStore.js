import axios from "@axios";
import { defineStore } from "pinia";

export const useDashboardStore = defineStore("DashboardStore", {
  actions: {
    async fetchNews(params) {
      return axios.get(
        `/news`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchNew({ id }) {
      return axios.get(`/news/${id}`, {
        validateStatus: () => true,
      });
    },

    async fetchDocumentDownloads(params) {
      return axios.get(
        `/document-download`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },
  },
});
