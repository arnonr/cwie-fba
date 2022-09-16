const Service = require("../services/student.service"),
    studentService = require("../services/student.service"),
    facultyService = require("../services/faculty.service"),
    departmentService = require("../services/department.service"),
    majorService = require("../services/major.service"),
    dbFaculty = require("../models/Faculty"),
    dbDepartment = require("../models/Department"),
    dbMajor = require("../models/Major"),
    jwt = require("jsonwebtoken");
    db = require("../models/Student")

const methods = {
    async onGetAll(req, res) {
        try {
            let result = await Service.find(req);

            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

    async onGetById(req, res) {
        try {
            let result = await Service.findById(req.params.id);
            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

    async onInsert(req, res) {
        try {
            const decoded = jwt.decode(req.headers.authorization.split(" ")[1]);
            // console.log(decoded)
            req.body.created_by = decoded.user_id;
            //   req.body.created_by = 1;
            let result = await Service.insert(req.body);

            res.success(result, 201);
        } catch (error) {
            res.error(error);
        }
    },

    async onUpdate(req, res) {
        try {
            const decoded = jwt.decode(req.headers.authorization.split(" ")[1]);
            req.body.updated_by = decoded.id;

            const result = await Service.update(req.params.id, req.body);
            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

    async onDelete(req, res) {
        try {
            await Service.delete(req.params.id);
            res.success("success", 204);
        } catch (error) {
            res.error(error);
        }
    },

    async onRegStudentInfo(req, res) {
        try {
        let result = await Service.regStudentInfo(req.params.id);
            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

    async onImportRegStudent(req, res) {
        const decoded = jwt.decode(req.headers.authorization.split(" ")[1]);
        try {
            let result = null;
            // let result = await Service.insertRegStudent(req.params.id);
            let regObj = await Service.regStudentInfo(req.params.id);

            let faculty_id = null;
            let department_id = null;
            let major_id = null;

            /* Faculty check */
            let facObj = await dbFaculty.findOne({
                where: { faculty_code: regObj.faculty_code },
            });

            if(facObj === null){
                try {
                    let facultyInsertObj = await facultyService.insert({
                        faculty_code: regObj.faculty_code,
                        name_th: regObj.faculty_name,
                        name_en: regObj.faculty_name,
                        created_by: decoded.user_id
                    });
                    faculty_id = facultyInsertObj.faculty_id;
                } catch (error) {
                    console.log(error);
                }
            }else{
                faculty_id = facObj.faculty_id;
            }
            /* !-- Faculty check */

            /* Department check */
            let deptObj = await dbDepartment.findOne({
                where: { department_code: regObj.department_code },
            });

            if(deptObj === null){
                try {
                    let deptInsertObj = await departmentService.insert({
                        department_code: regObj.department_code,
                        name_th: regObj.department_name,
                        name_en: regObj.department_name,
                        created_by: decoded.user_id,
                        faculty_id: faculty_id
                    });
                    department_id = deptInsertObj.department_id
                }catch (error) {
                    console.log(error);
                }
            }else{
                department_id = deptObj.department_id;
            }
            /* !-- Department check */

            /* Major check */
            let majorObj = await dbMajor.findOne({
                where: { major_code: regObj.division_code },
            });
            if(majorObj === null){
                try {
                    let majorInsertObj = await majorService.insert({
                        major_code: regObj.division_code,
                        name_th: regObj.division_name,
                        name_en: regObj.division_name,
                        created_by: decoded.user_id,
                        department_id: 	department_id
                    });
                    major_id = majorInsertObj.major_id
                }catch (error) {
                    console.log(error);
                }
            }else{
                major_id = majorObj.major_id;
            }

            const studentObj = await db.findOne({
                where: { student_code : regObj.student_code },
            });

            regObj['faculty_id'] = faculty_id;
            regObj['department_id'] = department_id;
            regObj['major_id'] = major_id;
            regObj['created_by'] = decoded.user_id;

            let saveObj = null;
            if (!studentObj) {
                saveObj = await studentService.insert(regObj);
            }else{
                saveObj = await studentService.update(studentObj.student_id, regObj);
            }

            res.success(saveObj);
        } catch (error) {
            res.error(error);
        }
    },

    // async onInsertRegStudent(req, res) {
    //     try {
    //     let result = await Service.insertRegStudent(req.params.id);
    //         res.success(result);
    //     } catch (error) {
    //         res.error(error);
    //     }
    // },
};

module.exports = { ...methods };
