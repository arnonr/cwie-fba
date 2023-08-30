export default [
  { heading: "ประธานอาจารย์นิเทศ", subject: "MajorHeadUser", action: "manage" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "major-head-students" },
    subject: "MajorHeadUser",
    action: "manage",
  },
  {
    title: "รายการออกนิเทศ",
    icon: { icon: "tabler-user" },
    to: { name: "major-head-visit" },
    subject: "MajorHeadUser",
    action: "manage",
  },
];
