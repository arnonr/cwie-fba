const Sequelize = require("sequelize"),
  { sequelize } = require("../configs/databases");

// // ห้าม force เป็น true เด็ดขาด ข้อมูลจะถูกรีเซต
// db.sequelize.sync({force: false}).then(() => {
//     console.log('yes re-sync done!')
// })

// const User = require("./User");
// const DocumentType = require("./DocumentType");
const Faculty = require("./faculty");
const Department = require("./department");

Department.associate({
  Faculty,
});

// Faculty.associate({
//   Department,
// });

// เคย error เรื่องของลำดับด้วย

(async () => {
  await sequelize.sync();
  // Code here
})();

// module.exports = db
