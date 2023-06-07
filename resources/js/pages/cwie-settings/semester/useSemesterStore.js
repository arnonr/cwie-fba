import axios from "@axios";
import { defineStore } from "pinia";

export const useSemesterStore = defineStore("SemesterStore", {
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

    fetchSemester({ id }) {
      return axios.get(`/semester/${id}`);
    },

    async addSemester(dataSend) {
      return await axios.post(`/semester`, dataSend, {
        validateStatus: () => true,
      });
    },

    async editSemester(dataSend) {
      return await axios.put(`/semester/${dataSend.id}`, dataSend, {
        validateStatus: () => true,
      });
    },

    async deleteSemester({ id }) {
      return await axios.delete(`/semester/${id}`, {
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
  },
});
