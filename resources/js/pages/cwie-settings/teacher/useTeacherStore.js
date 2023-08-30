import axios from "@axios";
import { defineStore } from "pinia";

export const useTeacherStore = defineStore("TeacherStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchTeachers(params) {
      return axios.get(
        "/teacher",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchTeacher({ id }) {
      return axios.get(`/teacher/${id}`);
    },

    async addTeacher({ teachername }) {
      return await axios.post(`/teacher/import-icit-account/${teachername}`, {
        validateStatus: () => true,
      });
    },

    async editTeacher(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }
      form_data.append("_method", "PUT");

      return await axios.post(`/teacher/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async deleteTeacher({ id }) {
      return await axios.delete(`/teacher/${id}`, {
        validateStatus: () => true,
      });
    },

    loadTeacher(params) {
      return axios.get(
        `/teacher/hris-find-personnel/`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    syncTeacher() {
      return axios.post(
        `/teacher/hris-sync-all-teacher`,
        {},
        {
          validateStatus: () => true,
        }
      );
    },

    fetchProvinces(params) {
      return axios.get(
        "/province",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchAmphurs(params) {
      return axios.get(
        "/amphur",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchTumbols(params) {
      return axios.get(
        "/tumbol",
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
