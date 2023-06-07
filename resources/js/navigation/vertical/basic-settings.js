export default [
  { heading: "ตั้งค่าระบบ", action: "read", subject: "Auth" },
  {
    title: "จัดการผู้ใช้งาน",
    icon: { icon: "tabler-users" },
    to: { name: "basic-settings-user" },
    action: "read",
    subject: "Auth",
  },
  {
    title: "จัดการค่านิเทศ/ค่าเดินทาง",
    icon: { icon: "tabler-square-letter-t" },
    to: { name: "basic-settings-province" },
    action: "read",
    subject: "Auth",
  },
];
