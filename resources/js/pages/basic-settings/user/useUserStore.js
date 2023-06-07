import axios from "@axios";
import { defineStore } from "pinia";

export const useUserStore = defineStore("UserStore", {
  actions: {
    // 👉 Fetch all Slide
    fetchUsers(params) {
      return axios.get(
        "/user",
        { params },
        {
          validateStatus: () => true,
        }
      );
    },

    fetchUser({ id }) {
      return axios.get(`/user/${id}`);
    },

    async editUser(dataSend) {
      return await axios.put(`/user/${dataSend.id}`, dataSend, {
        validateStatus: () => true,
      });
    },

    async addUser({ username }) {
      return await axios.post(`/user/import-icit-account/${username}`, {
        validateStatus: () => true,
      });
    },

    deleteUser({ id }) {
      return axios.delete(`/user/${id}`, {
        validateStatus: () => true,
      });
    },

    loadUser({ username }) {
      return axios.get(
        `/user/get-icit-account/${username}`,
        {},
        {
          validateStatus: () => true,
        }
      );
    },
  },
});
