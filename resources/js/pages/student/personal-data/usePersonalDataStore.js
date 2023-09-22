import axios from "@axios";
import { defineStore } from "pinia";

export const usePersonalDataStore = defineStore("PersonalDataStore", {
  actions: {
    fetchStudents(params) {
      return axios.get(
        `/student`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchStudent({ id }) {
      return axios.get(`/student/${id}`, {
        validateStatus: () => true,
      });
    },

    async editStudent(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }
      form_data.append("_method", "PUT");

      return await axios.post(`/student/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async deleteStudent({ id }) {
      return await axios.delete(`/student/${id}`, {
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

    fetchDocumentTypes(params) {
      return axios.get(
        `/document-type`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchStudentDocuments(params) {
      return axios.get(
        `/student-document`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async deleteStudentDocument({ id }) {
      return await axios.delete(`/student-document/${id}`, {
        validateStatus: () => true,
      });
    },

    async addStudentDocument(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }

      return await axios.post(`/student-document`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async editStudentDocument(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }
      form_data.append("_method", "PUT");

      return await axios.post(`/student-document/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async fetchForms(params) {
      return axios.get(
        `/form`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },
  },
});
