export default [
  { heading: "นักศึกษา", action: "manage", subject: "StudentUser" },
  {
    title: "ข้อมูลส่วนตัว",
    icon: { icon: "tabler-user" },
    to: { name: "student-personal-data" },
    action: "manage",
    subject: "StudentUser",
  },
  {
    title: "ข้อมูลสหกิจศึกษา",
    icon: { icon: "tabler-book" },
    to: { name: "student-cwie-data" },
    action: "manage",
    subject: "StudentUser",
  },
];
