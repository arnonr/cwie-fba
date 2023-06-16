import axios from "@axios";
import { defineStore } from "pinia";

export const useCwieDataStore = defineStore("CwieDataStore", {
  actions: {
    async fetchForms(params) {
      return axios.get(
        `/form`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

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
      //
      return await axios.put(`/student/${dataSend.id}`, dataSend, {
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

    fetchSemesters(params) {
      return axios.get(
        "/semester",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchCompanies(params) {
      return axios.get(
        "/company",
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

    async addRejectLog(dataSend) {
      return await axios.post(`/reject-log`, dataSend, {
        validateStatus: () => true,
      });
    },

    async approve(dataSend) {
      console.log(dataSend);
      return await axios.put(`/form/${dataSend.id}`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
