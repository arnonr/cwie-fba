import axios from "@axios";
import { defineStore } from "pinia";

export const useVisitApproveStore = defineStore("VisitApproveStore", {
  actions: {
    async addRejectLog(dataSend) {
      return await axios.post(`/visit-reject-log`, dataSend, {
        validateStatus: () => true,
      });
    },

    async approve(dataSend) {
      console.log(dataSend);
      return await axios.put(`/visit/approve/${dataSend.visit_id}`, dataSend, {
        validateStatus: () => true,
      });
    },

    async editVisit(dataSend) {
      return await axios.put(`/visit/${dataSend.visit_id}`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
