import axios from "@axios";
import { defineStore } from "pinia";

export const useCwieDataStore = defineStore("CwieDataStore", {
  actions: {
    async addVisit(dataSend) {
      return await axios.post(`/visit`, dataSend, {
        validateStatus: () => true,
      });
    },

    // async deleteVisit(dataSend) {
    //   return await axios.put(`/visit/${dataSend.visit_id}`, dataSend, {
    //     validateStatus: () => true,
    //   });
    // },

    async editVisit(dataSend) {
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

    //

    async fetchForms(params) {
      return axios.get(
        `/form`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async fetchTeachers(params) {
      return axios.get(
        `/teacher`,
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    async fetchVisits(params) {
      return axios.get(
        `/visit`,
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

    async addVisitRejectLog(dataSend) {
      return await axios.post(`/visit-reject-log`, dataSend, {
        validateStatus: () => true,
      });
    },
  },
});
