import axios from "@axios";
import { defineStore } from "pinia";

export const useMajorHeadStore = defineStore("MajorHeadStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchMajorHeads(params) {
      return axios.get(
        "/major-head",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchMajorHead({ id }) {
      return axios.get(`/major-head/${id}`);
    },

    async addMajorHead(dataSend) {
      return await axios.post(`/major-head`, dataSend, {
        validateStatus: () => true,
      });
    },

    async editMajorHead(dataSend) {
      return await axios.put(`/major-head/${dataSend.id}`, dataSend, {
        validateStatus: () => true,
      });
    },

    async deleteMajorHead({ id }) {
      return await axios.delete(`/major-head/${id}`, {
        validateStatus: () => true,
      });
    },

    fetchTeachers(params) {
      return axios.get(
        "/teacher",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchDepartments(params) {
      return axios.get(
        "/department",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchMajors(params) {
      return axios.get(
        "/major",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },
  },
});
