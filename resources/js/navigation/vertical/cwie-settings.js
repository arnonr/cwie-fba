export default [
  { heading: "ตั้งค่า COOP", action: "manage", subject: "AdminUser" },
  {
    title: "ข้อมูลอาจารย์",
    icon: { icon: "tabler-users" },
    to: { name: "cwie-settings-teacher" },
    action: "manage",
    subject: "AdminUser",
  },
  {
    title: "ข้อมูลสถานประกอบการ",
    icon: { icon: "tabler-users" },
    to: { name: "cwie-settings-company" },
    action: "manage",
    subject: "AdminUser",
  },
  {
    title: "ข้อมูลปีการศึกษา",
    icon: { icon: "tabler-users" },
    to: { name: "cwie-settings-semester" },
    action: "manage",
    subject: "AdminUser",
  },
];
