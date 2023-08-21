import axios from "@axios";
import { defineStore } from "pinia";

export const useVisitDownloadStore = defineStore("VisitDownloadStore", {
  actions: {
    // async addRejectLog(dataSend) {
    //   return await axios.post(`/visit-reject-log`, dataSend, {
    //     validateStatus: () => true,
    //   });
    // },

    // async approve(dataSend) {
    //   console.log(dataSend);
    //   return await axios.put(`/visit/approve/${dataSend.visit_id}`, dataSend, {
    //     validateStatus: () => true,
    //   });
    // },

    async fetchTeachers(params) {
      return axios.get(
        `/teacher`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async fetchMajorHeads(params) {
      return axios.get(
        `/major-head`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async fetchProvinces(params) {
      return axios.get(
        `/province`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async fetchAmphurs(params) {
      return axios.get(
        `/amphur`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async fetchTumbols(params) {
      return axios.get(
        `/tumbol`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async sendReport(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
      }
      form_data.append("_method", "PUT");

      return await axios.post(`/visit/${dataSend.visit_id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },
  },
});
