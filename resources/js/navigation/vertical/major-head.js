export default [
  { heading: "ประธานอาจารย์นิเทศ", subject: "TeacherUser", action: "manage" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "major-head-students" },
    subject: "TeacherUser",
    action: "manage",
  },
  {
    title: "รายการออกนิเทศ",
    icon: { icon: "tabler-user" },
    to: { name: "major-head-visit" },
    subject: "TeacherUser",
    action: "manage",
  },
];
