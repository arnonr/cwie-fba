import axios from "@axios";
import { defineStore } from "pinia";

export const useCompanyStore = defineStore("CompanyStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchCompanies(params) {
      return axios.get(
        "/company",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchCompany({ id }) {
      return axios.get(`/company/${id}`);
    },

    async addCompany(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
      }

      return await axios.post(`/company`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async editCompany(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }
      form_data.append("_method", "PUT");

      return await axios.post(`/company/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async deleteCompany({ id }) {
      return await axios.delete(`/company/${id}`, {
        validateStatus: () => true,
      });
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
