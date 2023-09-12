import axios from "@axios";
import { defineStore } from "pinia";

export const useStudentListStore = defineStore("StudentListStore", {
  actions: {
    fetchListStudents(params) {
      return axios.get(
        `/student`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchSemesters(params) {
      return axios.get(
        "/semester",
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

    fetchTeachers(params) {
      return axios.get(
        `/teacher`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchMajorHeads(params) {
      return axios.get(
        "/major-head",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async editSupervision(dataSend) {
      return await axios.put(
        `/form/edit-supervision/${dataSend.id}`,
        dataSend,
        {
          validateStatus: () => true,
        }
      );
    },

    async importSupervision(dataSend) {
      return await axios.post(`/form/import-form-supervision`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
