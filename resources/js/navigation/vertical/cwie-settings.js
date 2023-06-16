export default [
  { heading: "ตั้งค่า COOP", action: "read", subject: "Auth" },
  {
    title: "ข้อมูลอาจารย์",
    icon: { icon: "tabler-users" },
    to: { name: "cwie-settings-teacher" },
    action: "read",
    subject: "Auth",
  },
  {
    title: "ข้อมูลสถานประกอบการ",
    icon: { icon: "tabler-users" },
    to: { name: "cwie-settings-company" },
    action: "read",
    subject: "Auth",
  },
  {
    title: "ข้อมูลปีการศึกษา",
    icon: { icon: "tabler-users" },
    to: { name: "cwie-settings-semester" },
    action: "read",
    subject: "Auth",
  },
];
