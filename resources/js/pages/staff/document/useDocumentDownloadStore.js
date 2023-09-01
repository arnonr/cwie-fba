import axios from "@axios";
import { defineStore } from "pinia";

export const useDocumentDownloadStore = defineStore("DocumentDownloadStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchDocumentDownloads(params) {
      return axios.get(
        "/document-download",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchDocumentDownload({ id }) {
      return axios.get(`/document-download/${id}`);
    },

    async addDocumentDownload(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        form_data.append(key, dataSend[key]);
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
      }

      return await axios.post(`/document-download`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async editDocumentDownload(dataSend) {
      var form_data = new FormData();

      for (var key in dataSend) {
        if (dataSend[key] == null) {
          dataSend[key] = "";
        }
        form_data.append(key, dataSend[key]);
      }

      form_data.append("_method", "PUT");

      return await axios.post(`/document-download/${dataSend.id}`, form_data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        validateStatus: () => true,
      });
    },

    async deleteDocumentDownload({ id }) {
      return await axios.delete(`/document-download/${id}`, {
        validateStatus: () => true,
      });
    },
  },
});
