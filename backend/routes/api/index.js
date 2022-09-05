const router = require("express").Router();
const auth = require("../auth");
console.log("SK");
router.use("/user", require("./user"));
router.use("/document-type", require("./documentType"));
router.use("/faculty", require("./faculty"));
router.use("/department", require("./department"));
router.use("/teacher", require("./teacher"));

module.exports = router;

