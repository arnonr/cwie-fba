import axios from "@axios";
import { defineStore } from "pinia";

export const useProvinceStore = defineStore("ProvinceStore", {
  actions: {
    
    // 👉 Fetch all Slide
    fetchProvinces(params) {
      return axios.get("/province", { params },{
        validateStatus: () => true,
      });
    },

    fetchProvince({ id }) {
      return axios.get(`/province/${id}`);
    },
    
    async editProvince(dataSend) {
        return await axios.put(`/province/${dataSend.province_id}`, dataSend,{
            validateStatus: () => true,
          });
    },
    
  },
});
