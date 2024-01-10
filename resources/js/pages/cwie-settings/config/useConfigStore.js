import axios from "@axios";
import { defineStore } from "pinia";

export const useConfigStore = defineStore("ConfigStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchSemesters(params) {
      return axios.get(
        "/semester",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchConfig({ id }) {
      return axios.get(`/config/${id}`);
    },

    async editConfig(dataSend) {
      return await axios.put(`/config/${dataSend.setting_id}`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
