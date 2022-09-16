const Service = require("../services/teacher.service"),
    dbFaculty = require("../models/Faculty"),
    dbDepartment = require("../models/Department"),
    facultyService = require("../services/faculty.service"),
    departmentService = require("../services/department.service"),
    jwt = require("jsonwebtoken");

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

    async onHrisPersonnelInfo(req, res) {
        const decoded = jwt.decode(req.headers.authorization.split(" ")[1]);

        try {
            let result = await Service.hrisPersonnelInfo(req.params.id);

            let faculty_id = null;
            let department_id = null;
            let facObj = await dbFaculty.findOne({
                where: { faculty_code: result.faculty_code },
            });

            if(facObj === null){
                try {
                    let facultyInsertObj = await facultyService.insert({
                        faculty_code: result.faculty_code,
                        name_th: result.faculty_name,
                        name_en: result.faculty_name,
                        created_by: decoded.user_id
                    });
                    faculty_id = facultyInsertObj.faculty_id;
                } catch (error) {
                    console.log(error);
                }
            }else{
                faculty_id = facObj.faculty_id;
            }

            let deptObj = await dbDepartment.findOne({
                where: { department_code: result.department_code },
            });
            if(deptObj === null){
                try {
                    let deptInsertObj = await departmentService.insert({
                        department_code: result.department_code,
                        name_th: result.department_name,
                        name_en: result.department_name,
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

            result['faculty_id'] = faculty_id;
            result['department_id'] = department_id;

            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

    async onHrisFindPersonnel(req, res) {
        try {
        let result = await Service.hrisFindPersonnel(req.body);

            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

};

module.exports = { ...methods };
