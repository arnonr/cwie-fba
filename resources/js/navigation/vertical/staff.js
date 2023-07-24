export default [
  {
    title: "หน้าหลัก",
    icon: { icon: "tabler-home" },
    to: { name: "dashboards" },
    action: "manage",
    subject: "Auth",
  },
  { heading: "เจ้าหน้าที่คณะ", action: "manage", subject: "StaffUser" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "staff-students" },
    action: "manage",
    subject: "StaffUser",
  },
  {
    title: "หนังสือขอความอนุเคราะห์",
    icon: { icon: "tabler-user" },
    to: { name: "staff-book1" },
    action: "manage",
    subject: "StaffUser",
  },
  {
    title: "หนังสือส่งตัว",
    icon: { icon: "tabler-user" },
    to: { name: "staff-book2" },
    action: "manage",
    subject: "StaffUser",
  },
  {
    title: "จับคู่อาจารย์นิเทศ",
    icon: { icon: "tabler-user" },
    to: { name: "staff-map-supervisor" },
    action: "manage",
    subject: "StaffUser",
  },
];
