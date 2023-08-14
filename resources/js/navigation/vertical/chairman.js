export default [
  {
    heading: "ประธานบริหารโครงหารสหกิจ",
    subject: "TeacherUser",
    action: "manage",
  },
  {
    title: "รายการนักศึกษา",
    icon: { icon: "tabler-user" },
    to: { name: "chairman-students" },
    subject: "TeacherUser",
    action: "manage",
  },
  {
    title: "รายการออกนิเทศ",
    icon: { icon: "tabler-user" },
    to: { name: "chairman-visit" },
    subject: "TeacherUser",
    action: "manage",
  },
];
