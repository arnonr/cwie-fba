export default [
  { heading: "ตั้งค่าระบบ", action: "manage", subject: "AdminUser" },
  {
    title: "จัดการผู้ใช้งาน",
    icon: { icon: "tabler-users" },
    to: { name: "basic-settings-user" },
    action: "manage",
    subject: "AdminUser",
  },
  {
    title: "จัดการค่านิเทศ/ค่าเดินทาง",
    icon: { icon: "tabler-square-letter-t" },
    to: { name: "basic-settings-province" },
    action: "manage",
    subject: "AdminUser",
  },
];
