export default [
  { heading: "เจ้าหน้าที่คณะ", action: "read", subject: "Auth" },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "staff-students" },
    action: "read",
    subject: "Auth",
  },
  {
    title: "หนังสือขอความอนุเคราะห์",
    icon: { icon: "tabler-user" },
    to: { name: "staff-book1" },
    action: "read",
    subject: "Auth",
  },
];
