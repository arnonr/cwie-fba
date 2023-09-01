import axios from "@axios";
import { defineStore } from "pinia";

export const useStudentActionStore = defineStore("StudentActionStore", {
  actions: {
    async addRejectLog(dataSend) {
      return await axios.post(`/reject-log`, dataSend, {
        validateStatus: () => true,
      });
    },

    async approve(dataSend) {
      console.log(dataSend);
      return await axios.put(`/form/approve/${dataSend.id}`, dataSend, {
        validateStatus: () => true,
      });
    },

    async editForm(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
      }
      form_data.append("_method", "PUT");

      return await axios.post(`/form/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    //
    fetchCompany({ id }) {
      return axios.get(
        `/company/${id}`,
        {},
        {
          validateStatus: () => true,
        }
      );
    },

    fetchTeacher({ id }) {
      return axios.get(
        `/teacher/${id}`,
        {},
        {
          validateStatus: () => true,
        }
      );
    },

    async addResponseBook(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }
      return await axios.post(`/form/add-response-book`, form_data, {
        validateStatus: () => true,
      });
    },

    async addPlan(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }
      return await axios.post(`/form/add-plan`, form_data, {
        validateStatus: () => true,
      });
    },
  },
});
