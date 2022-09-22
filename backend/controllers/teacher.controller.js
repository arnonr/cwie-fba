const Service = require("../services/teacher.service"),
    dbFaculty = require("../models/Faculty"),
    dbDepartment = require("../models/Department"),
    facultyService = require("../services/faculty.service"),
    departmentService = require("../services/department.service"),
    jwt = require("jsonwebtoken"),
    db = require("../models/Teacher");

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

    async onHrisSyncAllTeacher(req, res) {
        const decoded = jwt.decode(req.headers.authorization.split(" ")[1]);
        let user_id = decoded.user_id;
        try {
            // let result = await Service.hrisFindPersonnel({position_type_id : 1, person_key:2009985010791});
            let result = await Service.hrisFindPersonnel({position_type_id : 1});
            for(var key in result) {
                console.log(key, result[key]['person_key']);

                let person_key = result[key]['person_key'];
                let resultInfo = await Service.hrisPersonnelInfo({person_key:person_key});

                const faculty = await facultyService.importFaculty({faculty_code:resultInfo.faculty_code, faculty_name:resultInfo.faculty_name_th, user_id:user_id});
                let faculty_id = faculty.faculty_id;

                const department = await departmentService.importDepartment({department_code:resultInfo.department_code, department_name:resultInfo.departement_name_th, faculty_id:faculty_id, user_id:user_id});

                let department_id = department.department_id;

                const teacherObj = await db.findOne({
                    where: { citizen_id : resultInfo.citizen_id },
                });

                resultInfo['person_key'] = resultInfo.person_key;
                resultInfo['citizen_id'] = resultInfo.citizen_id;
                resultInfo['faculty_id'] = faculty_id;
                resultInfo['department_id'] = department_id;

                let saveObj = null;
                if (!teacherObj) {
                    resultInfo['created_by'] = user_id;
                    saveObj = await Service.insert(resultInfo);

                }else{
                    saveObj = await Service.update(teacherObj.teacher_id, resultInfo);
                }
                // console.log(resultInfo);
            }
            res.success(result);
        } catch (error) {
            res.error(error);
        }
    },

};

module.exports = { ...methods };
