const config = require("../configs/app"),
  db = require("../models/User"),
  jwt = require("jsonwebtoken"),
  {
    ErrorBadRequest,
    ErrorNotFound,
    ErrorUnauthorized,
  } = require("../configs/errorMethods"),
  { Op } = require("sequelize");
const nodemailer = require("nodemailer");

const methods = {
  scopeSearch(req, limit, offset) {
    // Where
    $where = {};

    if (req.query.user_id) $where["user_id"] = req.query.user_id;

    if (req.query.username)
      $where["username"] = {
        [Op.like]: "%" + req.query.username + "%",
      };

    if (req.query.name)
      $where["name"] = {
        [Op.like]: "%" + req.query.name + "%",
      };

    if (req.query.tel)
      $where["tel"] = {
        [Op.like]: "%" + req.query.tel + "%",
      };

    if (req.query.email)
      $where["email"] = {
        [Op.like]: "%" + req.query.email + "%",
      };

    if (req.query.citizen_id)
      $where["citizen_id"] = {
        [Op.like]: "%" + req.query.citizen_id + "%",
      };

    if (req.query.account_type) $where["account_type"] = req.query.account_type;

    const query = Object.keys($where).length > 0 ? { where: $where } : {};

    // Order
    $order = [["username", "ASC"]];
    if (req.query.orderByField && req.query.orderBy)
      $order = [
        [
          req.query.orderByField,
          req.query.orderBy.toLowerCase() == "desc" ? "desc" : "asc",
        ],
      ];
    query["order"] = $order;

    query["include"] = [{ all: true, required: false }];

    if (!isNaN(limit)) query["limit"] = limit;

    if (!isNaN(offset)) query["offset"] = offset;

    return { query: query };
  },

  find(req) {
    const limit = +(req.query.size || config.pageLimit);
    const offset = +(limit * ((req.query.page || 1) - 1));
    const _q = methods.scopeSearch(req, limit, offset);
    return new Promise(async (resolve, reject) => {
      try {
        Promise.all([db.findAll(_q.query), db.count(_q.query)])
          .then((result) => {
            let rows = result[0],
              count = rows.length;

            resolve({
              total: count,
              lastPage: Math.ceil(count / limit),
              currPage: +req.query.page || 1,
              rows: rows,
            });
          })
          .catch((error) => {
            reject(error);
          });
      } catch (error) {
        reject(error);
      }
    });
  },

  findById(id) {
    return new Promise(async (resolve, reject) => {
      try {
        let obj = await db.findByPk(id, {
          include: [{ all: true, required: false }],
        });

        if (!obj) reject(ErrorNotFound("id: not found"));

        resolve(obj.toJSON());
      } catch (error) {
        reject(ErrorNotFound("id: not found"));
      }
    });
  },

  insert(data) {
    return new Promise(async (resolve, reject) => {
      try {
        const obj = new db(data);
        obj.Password = obj.passwordHash(obj.Password);
        const inserted = await obj.save();

        // let transporter = nodemailer.createTransport({
        //   host: "smtp.gmail.com",
        //   port: 587,
        //   secure: false,
        //   auth: {
        //     // ข้อมูลการเข้าสู่ระบบ
        //     user: "edocument@fba.kmutnb.ac.th", // email user ของเรา
        //     pass: "edoc2565", // email password
        //   },
        // });

        // let info = await transporter.sendMail({
        //   from: '"ระบบฐานข้อมูลโคเนื้อ กระบือ แพะ', // อีเมลผู้ส่ง
        //   to: obj.Username, // อีเมลผู้รับ สามารถกำหนดได้มากกว่า 1 อีเมล โดยขั้นด้วย ,(Comma)
        //   subject: "ระบบฐานข้อมูล โคเนื้อ กระบิอ แพะ", // หัวข้ออีเมล
        //   // text: "d", // plain text body
        //   html: "<b>คุณได้รับการอนุมัติการเข้าใช้งานระบบฐานข้อมูล โคเนื้อ กระบือ แพะ สามารถเข้าใช้งานได้ที่ <a href='http://178.128.216.177/'>คลิก</a></b>", // html body
        // });

        let res = methods.findById(inserted.user_id);

        resolve(res);
      } catch (error) {
        reject(ErrorBadRequest(error.message));
      }
    });
  },

  update(id, data) {
    return new Promise(async (resolve, reject) => {
      try {
        // Check ID
        const obj = await db.findByPk(id);
        if (!obj) reject(ErrorNotFound("id: not found"));

        // Update
        data.user_id = parseInt(id);

        if (data.Password) {
          data.Password = obj.passwordHash(data.Password);
        }

        await db.update(data, { where: { user_id: id } });

        let res = methods.findById(obj.user_id);

        resolve(res);
      } catch (error) {
        reject(ErrorBadRequest(error.message));
      }
    });
  },

  delete(id) {
    return new Promise(async (resolve, reject) => {
      try {
        const obj = await db.findByPk(id);
        if (!obj) reject(ErrorNotFound("id: not found"));

        await db.update(
          { isRemove: 1, isActive: 0 },
          { where: { user_id: id } }
        );

        const obj1 = UserToAnimalType.destroy({
          where: { user_id: id },
          // // truncate: true,
        });

        resolve();
      } catch (error) {
        reject(error);
      }
    });
  },

  login(data, ip, device) {
    return new Promise(async (resolve, reject) => {
      try {
        const obj = await db.findOne({
          where: { username: data.username },
          include: { all: true },
        });

        // checkICIT ACCOUNT
        //

        // ตรวจสอบว่ามี username
        if (!obj) {
          reject(ErrorUnauthorized("Username not found"));
        } else {
          // ตรวจสอบ Password
          if (!obj.validPassword(data.password)) {
            reject(ErrorUnauthorized("Password is invalid."));
          }
        }

        res = {
          ...obj.toJSON(),
        };

        resolve({ accessToken: obj.generateJWT(obj), userData: res });
      } catch (error) {
        reject(error);
      }
    });
  },

  register(data) {
    return new Promise(async (resolve, reject) => {
      try {



        const obj = new db(data);
        obj.Password = obj.passwordHash(obj.Password);
        const inserted = await obj.save();

        // Send mail
        let transporter = nodemailer.createTransport({
          host: "smtp.gmail.com",
          port: 587,
          secure: false,
          auth: {
            // ข้อมูลการเข้าสู่ระบบ
            user: "edocument@fba.kmutnb.ac.th", // email user ของเรา
            pass: "edoc2565", // email password
          },
        });

        let info = await transporter.sendMail({
          from: '"ระบบฐานข้อมูลโคเนื้อ กระบือ แพะ', // อีเมลผู้ส่ง
          to: inserted.Username, // อีเมลผู้รับ สามารถกำหนดได้มากกว่า 1 อีเมล โดยขั้นด้วย ,(Comma)
          subject: "ระบบฐานข้อมูล โคเนื้อ กระบือ แพะ", // หัวข้ออีเมล
          // text: "d", // plain text body
          html: "<b>ระบบฐานข้อมูล โคเนื้อ กระบิอ แพะ ได้รับข้อมูลของท่านเรียบร้อยแล้ว อยู่ระหว่างรอการอนุมัติ", // html body
        });

        let res = methods.findById(inserted.user_id);

        resolve(res);
      } catch (error) {
        reject(ErrorBadRequest(error.message));
      }
    });
  },

  refreshToken(accessToken) {
    return new Promise(async (resolve, reject) => {
      try {
        const decoded = jwt.decode(accessToken);
        const obj = await db.findOne({
          where: { Username: decoded.Username },
          include: [
            { all: true, nested: true },
          ],
        });

        if (!obj) {
          reject(ErrorUnauthorized("Username not found"));
        }
        resolve({ accessToken: obj.generateJWT(obj), userData: obj });
      } catch (error) {
        reject(error);
      }
    });
  },
};

module.exports = { ...methods };
