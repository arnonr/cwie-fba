export default [
  { heading: "นักศึกษา", action: "read", subject: "Auth" },
  {
    title: "ข้อมูลส่วนตัว",
    icon: { icon: "tabler-user" },
    to: { name: "student-personal-data" },
    action: "read",
    subject: "Auth",
  },
  {
    title: "ข้อมูลสหกิจศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "student-cwie-data" },
    action: "read",
    subject: "Auth",
  },
];
