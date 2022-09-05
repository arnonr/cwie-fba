const { Model, DataTypes } = require("sequelize"),
  { sequelize } = require("../configs/databases");

const Faculty = require("./Faculty");

class Department extends Model {
  // Custom JSON Response
  //   toJSON() {
  //     return {
  //       ...this.get(),
  //     };
  //   }
}

Department.init(
  {
    department_id: {
      type: DataTypes.INTEGER,
      autoIncrement: true,
      primaryKey: true,
      allowNull: false,
      comment: "รหัสภาควิชา",
    },
    department_code: {
      type: DataTypes.STRING(4),
      allowNull: false,
      comment: "รหัสภาควิชา",
    },
    name_th: {
      type: DataTypes.STRING(100),
      allowNull: false,
      comment: "ชื่อภาควิชา (ไทย)",
    },
    name_en: {
      type: DataTypes.STRING(100),
      allowNull: false,
      comment: "ชื่อภาควิชา (อังกฤษ)",
    },
    tel: {
      type: DataTypes.STRING(32),
      comment: "เบอร์โทรศัพท์",
    },
    fax: {
      type: DataTypes.STRING(32),
      comment: "โทรสาร",
    },
    email: {
      type: DataTypes.STRING(32),
      comment: "อีเมล",
    },
    faculty_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      comment: "รหัสคณะ",
    },
    active: {
      type: DataTypes.TINYINT(1),
      allowNull: false,
      defaultValue: 1,
      comment: "1 = เปิดการใช้งาน / 0 = ปิดการใช้งาน",
    },
    createdAt: {
      field: "created_at",
      type: DataTypes.DATE,
      allowNull: false,
      comment: "วันที่เพิ่มข้อมูล",
    },
    created_by: {
      type: DataTypes.INTEGER,
      allowNull: false,
      comment: "ผู้เพิ่มข้อมูล",
    },
    updatedAt: {
      field: "updated_at",
      type: DataTypes.DATE,
      allowNull: true,
      comment: "วันที่แก้ไขข้อมูลล่าสุด",
    },
    updated_by: {
      type: DataTypes.INTEGER,
      allowNull: true,
      comment: "ผู้แก้ไขข้อมูลล่าสุด",
    },
    deletedAt: {
      field: "deleted_at",
      type: DataTypes.DATE,
      allowNull: true,
      comment: "วันที่ลบข้อมูล",
    },
  },
  {
    sequelize,
    timestamps: true,
    freezeTableName: true,
    paranoid: true,
    modelName: "department" /* ชื่อตาราง */,
  }
);

Department.belongsTo(Faculty, { foreignKey: "faculty_id" });

module.exports = Department;
