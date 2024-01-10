import axios from "@axios";
import { defineStore } from "pinia";

export const useConfigDefaultStore = defineStore("ConfigDefaultStore", {
  state: () => {
    config: {
    }
  },
  actions: {
    // 👉 Fetch all Slide
    async fetchConfig() {
      await axios.get(`/config/1`).then((res) => {
        // console.log(res.data.data);
        this.config = res.data.data;
        return true;
      });

      //   console.log(this.config);
    },
  },
});
