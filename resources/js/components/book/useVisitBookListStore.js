import axios from "@axios";
import { defineStore } from "pinia";

export const useVisitBookListStore = defineStore("VisitBookListStore", {
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

    async addVisitBook(dataSend) {
      return await axios.post(`/visit/add-visit-book`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
