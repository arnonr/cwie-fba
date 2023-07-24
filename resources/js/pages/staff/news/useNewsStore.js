import axios from "@axios";
import { defineStore } from "pinia";

export const useNewsStore = defineStore("NewsStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchNewses(params) {
      return axios.get(
        "/news",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchNews({ id }) {
      return axios.get(`/news/${id}`);
    },

    async addNews(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
      }

      return await axios.post(`/news`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async editNews(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }

      form_data.append("_method", "PUT");

      return await axios.post(`/news/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async deleteNews({ id }) {
      return await axios.delete(`/news/${id}`, {
        validateStatus: () => true,
      });
    },
  },
});
