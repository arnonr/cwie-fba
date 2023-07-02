export default [
  { heading: "อาจารย์ที่ปรึกษา", subject: "TeacherUser", action: "manage" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "teacher-students" },
    subject: "TeacherUser",
    action: "manage",
  },
];
