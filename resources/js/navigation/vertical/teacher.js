export default [
  { heading: "อาจารย์ที่ปรึกษา", subject: "TeacherUser", action: "manage" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "teacher-students" },
    subject: "TeacherUser",
    action: "manage",
  },
  { heading: "อาจารย์นิเทศ", subject: "TeacherUser", action: "manage" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "supervisor-students" },
    subject: "TeacherUser",
    action: "manage",
  },
  {
    title: "ขอออกนิเทศ",
    icon: { icon: "tabler-user" },
    to: { name: "supervisor-visit" },
    subject: "TeacherUser",
    action: "manage",
  },
];
