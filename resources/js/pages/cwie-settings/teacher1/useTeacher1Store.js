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

    async editTeacher(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
      }
      form_data.append("_method", "PUT");

      return await axios.put(`/teacher/${dataSend.teacher_id}`, form_data, {
        headers: {
          "content-type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async addTeacher({ teachername }) {
      return await axios.post(`/teacher/import-icit-account/${teachername}`, {
        validateStatus: () => true,
      });
    },

    deleteTeacher({ id }) {
      return axios.delete(`/teacher/${id}`, {
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

    fetchDepartments(params) {
      return axios.get(
        "/department",
        { params },
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
  },
});
