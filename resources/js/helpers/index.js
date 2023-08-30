// fetchProvinces(params) {
//     return
//   }

export const getProvince = (province_id) => {
  if (province_id == null) return "";
  let res = provinces.value.find((e) => {
    return e.province_id == province_id;
  });
  return res.name_th;
};

// export const getProvince(province_id) {
//   return 1;
// }

// module.exports = getProvince;
// export default {
//   getProvince,
// };
