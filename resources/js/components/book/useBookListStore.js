import axios from "@axios";
import { defineStore } from "pinia";

export const useBookListStore = defineStore("BookListStore", {
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

    async addRequestBook(dataSend) {
      return await axios.post(`/form/add-request-book`, dataSend, {
        validateStatus: () => true,
      });
    },

    async addSendBook(dataSend) {
      return await axios.post(`/form/add-send-book`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
