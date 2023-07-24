export default [
  {
    title: "หน้าหลัก",
    icon: { icon: "tabler-home" },
    to: { name: "dashboards" },
    action: "manage",
    subject: "StaffUser",
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
    icon: { icon: "tabler-book" },
    to: { name: "staff-book1" },
    action: "manage",
    subject: "StaffUser",
  },
  {
    title: "หนังสือส่งตัว",
    icon: { icon: "tabler-book" },
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

  { heading: "ข่าวและเอกสารดาวน์โหลด", action: "manage", subject: "StaffUser" },
  {
    title: "รายการข่าว",
    icon: { icon: "tabler-news" },
    to: { name: "staff-news" },
    action: "manage",
    subject: "StaffUser",
  },
  {
    title: "เอกสารดาวน์โหลด",
    icon: { icon: "tabler-file" },
    to: { name: "staff-document" },
    action: "manage",
    subject: "StaffUser",
  },
];
