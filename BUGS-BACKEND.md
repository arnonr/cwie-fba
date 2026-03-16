# BUGS-BACKEND.md — CWIE-FBA Backend Issues

> Last updated: 2026-03-16

---

## Critical

- [ ] **B-S1** — Remove hardcoded backdoor password `"2023@COOP"` from `AuthController.php` (lines 43, 52, 112)
  > มีรหัสผ่านสำรองถูก hardcode ไว้ในโค้ด ใครก็ตามที่รู้รหัส `"2023@COOP"` สามารถ login เข้าระบบในนามของผู้ใช้คนใดก็ได้โดยไม่ต้องมีรหัสผ่านจริง ต้องลบออกทันที

- [ ] **B-S2** — Move KMUTNB API token to `.env` and rotate the exposed token (`AuthController.php:137`, `UserController.php:277`)
  > API Token ของระบบ KMUTNB ถูก hardcode ไว้ในโค้ดโดยตรง ผู้ที่เข้าถึง source code ได้จะสามารถนำ token ไปใช้งานได้ทันที ต้องย้ายไปไว้ใน `.env` และขอ token ใหม่เพราะ token เดิมถูกเปิดเผยแล้ว

- [ ] **B-S3** — Add `auth:api` middleware to all non-login API route groups in `routes/api.php`
  > เกือบทุก API endpoint (student, form, visit, company, teacher ฯลฯ) ไม่มีการตรวจสอบ token ก่อนเข้าถึง ผู้ใช้ที่ไม่ได้ login สามารถอ่าน แก้ไข หรือลบข้อมูลได้ทั้งหมด ต้องเพิ่ม `auth:api` middleware ให้ครอบทุก route ที่ต้องการการยืนยันตัวตน

- [ ] **B-S4** — Fix mass assignment in `UserController::edit()` — replace `update($request->all())` with explicit validated fields
  > ฟังก์ชัน edit ของ User ส่ง `$request->all()` เข้า database โดยตรง ผู้โจมตีสามารถแนบ field อื่น เช่น `account_type=0` หรือ `password=xxx` เพื่อเปลี่ยนสิทธิ์หรือรหัสผ่านของผู้ใช้คนอื่นได้

- [ ] **B-S7** — Add `auth:api` middleware to `/froala/*` upload endpoints (`routes/api.php:297-301`)
  > endpoint สำหรับอัปโหลดไฟล์ผ่าน Froala Editor (`/api/froala/image`, `/api/froala/document`, `/api/froala/video`) ไม่มีการตรวจสอบการ login เลย ใครก็ตามบนอินเทอร์เน็ตสามารถอัปโหลดไฟล์ขึ้น server ได้

- [ ] **B-S8** — Add MIME-type/extension validation and UUID-based filenames to all file upload handlers (`FroalaController.php`, `CompanyController.php`, `VisitController.php`, `StudentDocumentController.php`)
  > การอัปโหลดไฟล์ไม่มีการตรวจสอบประเภทไฟล์ (MIME type / extension) เลย สามารถอัปโหลดไฟล์ `.php` หรือ webshell ขึ้นไปได้ นอกจากนี้ชื่อไฟล์ยังใช้ `rand(10, 100)` ซึ่งมีแค่ 90 ค่า เดาได้ง่ายมาก ควรใช้ UUID แทนและตรวจสอบ MIME type ก่อนบันทึก

---

## High

- [ ] **B-B3** — Fix duplicate `const whitelist` declarations across 10 controller files — causes fatal PHP "Constant already defined" error (`StudentController.php:17`, `FroalaController.php:12`, `FormController.php:16`, `CompanyController.php:13`, `VisitController.php:14`, `TeacherController.php:17`, `NewsController.php:12`, `SemesterController.php:11`, `DocumentDownloadController.php:13`, `StudentDocumentController.php:14`)
  > มีการประกาศ `const whitelist` ซ้ำกันใน 10 ไฟล์ controller PHP ไม่อนุญาตให้ประกาศ constant ชื่อเดียวกันสองครั้งในขอบเขตเดียวกัน เมื่อ Laravel โหลด controller มากกว่าหนึ่งตัวใน request เดียว จะเกิด fatal error "Constant already defined" ทันที ควรย้ายไปไว้ใน `config/app.php` ที่เดียว

- [ ] **B-B5** — Add null check for `$teacher` in `AuthController::login()` before accessing `$teacher->id` — crashes if teacher record is missing (`AuthController.php:82`)
  > หากผู้ใช้ที่มี `account_type == 2` ยังไม่มีข้อมูลในตาราง `teacher` โค้ดจะพยายาม access `$teacher->id` บน `null` ทำให้ระบบ login พัง (fatal error) ต้องเพิ่มการตรวจสอบ null ก่อน

- [ ] **B-S9** — Add authentication, email validation, and rate limiting to `GET /api/mail/send/{receiver}`; move SMTP credentials to `.env` (`MailController.php:14,21`)
  > endpoint ส่งอีเมลรับ email ผู้รับมาจาก URL path โดยตรง ไม่มีการ login ไม่มีการตรวจสอบ email และไม่มี rate limiting ใครก็ได้สามารถใช้ระบบนี้ส่งอีเมลจากชื่อมหาวิทยาลัยไปยังที่อยู่ใดก็ได้ (open mail relay) นอกจากนี้ข้อมูล SMTP ยังถูก hardcode ไว้ในโค้ดด้วย

---

## Medium

- [ ] **B-B1** — Fix invalid validation syntax `["id as required"]` in 18 controller `edit()` methods — validates nothing (`UserController.php:240`, `FormController.php:713,958,1009,1065,1116,1166,1241`, `CompanyController.php:327`, `SemesterController.php:241`, `StudentController.php:745`, `StudentDocumentController.php:191`, `VisitRejectLogController.php:121`, `ConfigController.php:39`, `ProvinceController.php:105`, `NewsController.php:185`, `RejectLogController.php:137`, `DocumentDownloadController.php:184`)
  > การเขียน `$request->validate(["id as required"])` ผิด syntax ของ Laravel validation `"id as required"` จะถูกตีความเป็น key ไม่ใช่ rule ทำให้ไม่มีการ validate อะไรเลย รูปแบบที่ถูกต้องคือ `["id" => "required"]`

- [ ] **B-B4** — Return HTTP `401` (not `200`) for failed authentication in `AuthController::login()` (`AuthController.php:54-59`)
  > เมื่อ login ไม่สำเร็จ ระบบยังคืน HTTP status `200 OK` พร้อม body `{"message": "Unauthorized"}` ทำให้ client ที่ตรวจสอบ status code แยกไม่ออกระหว่างสำเร็จและไม่สำเร็จ ควรคืน `401 Unauthorized` แทน

- [ ] **B-B2** — Fix typo: `$request->active ? active : 1` should be `$request->active ?? 1` (`StudentDocumentController.php:177`)
  > `active` (ไม่มี `$`) ใน PHP จะถูกแปลเป็น string `"active"` ไม่ใช่ค่าจาก request ทำให้ `$item->active` ถูก set เป็น string `"active"` แทนค่าที่ส่งมาจริง

- [ ] **B-Q1** — Replace hardcoded `created_by = "arnonr"` / `updated_by = "arnonr"` with `Auth::id()` across 35+ locations in all controllers
  > ข้อมูล audit trail (`created_by`, `updated_by`) ถูก hardcode เป็นชื่อนักพัฒนา `"arnonr"` ใน 35+ จุดทั่วทั้งระบบ ทำให้ไม่สามารถติดตามได้ว่าใครเป็นคนแก้ไขข้อมูลจริง ควรใช้ `Auth::id()` แทน

- [ ] **B-Q4** — Implement server-side role/authorization checks in controllers — currently CASL is frontend-only with no backend enforcement
  > การตรวจสอบสิทธิ์ (CASL) ทำงานเฉพาะฝั่ง frontend เท่านั้น ไม่มีการตรวจสอบ role หรือสิทธิ์ใด ๆ ในฝั่ง backend เลย ผู้ใช้ที่ login แล้วสามารถเรียก API ของ admin หรือแก้ไขข้อมูลของผู้อื่นได้โดยตรง

---

## Low / Quality

- [ ] **B-P1** — Add Eloquent eager loading (`with()`) instead of manual `leftJoin()` chains duplicated across every controller
  > ทุก controller เขียน `leftJoin()` ซ้ำกันทุกครั้งแทนที่จะใช้ Eloquent relationship และ `with()` ทำให้โค้ดซ้ำซ้อน ดูแลยาก และเสี่ยงต่อ N+1 query หาก logic เพิ่มขึ้นในอนาคต

- [ ] **B-P2** — Add pagination to all `getAll()` endpoints — currently returns entire table with no limit
  > ทุก `getAll()` คืนข้อมูลทั้งตารางโดยไม่มี pagination เมื่อข้อมูลนักศึกษาหรือ form สะสมหลาย semester response จะใหญ่มากและทำให้ระบบช้า ควรเพิ่ม `paginate()` และรับ `per_page` จาก query parameter

- [ ] **B-P3** — Remove redundant manual `->where("deleted_at", null)` filters where `SoftDeletes` trait handles it automatically
  > มีการเขียน `->where("deleted_at", null)` ซ้ำซ้อนทั่วทั้งโค้ด ทั้งที่ถ้า model ใช้ `SoftDeletes` trait Laravel จะกรองค่านี้ให้อัตโนมัติอยู่แล้ว ควรตรวจสอบและลบออก

- [ ] **B-Q2** — Move SMTP sender/password from `MailController.php` to `config/mail.php` and `.env`
  > อีเมลผู้ส่งและรหัสผ่าน SMTP ถูก hardcode ไว้ใน `MailController.php` โดยตรง ควรย้ายไปไว้ใน `.env` และใช้ Laravel Mail facade แทน PHPMailer โดยตรง

- [ ] **B-Q3** — Extract `whitelist` array to a single shared config value (e.g. `config/app.php`) instead of file-scoped PHP constants
  > ค่า `whitelist` IP ถูกประกาศซ้ำกัน 10 ครั้งในไฟล์ต่างกัน ควรย้ายไปไว้ที่เดียวใน `config/app.php` เพื่อให้แก้ไขได้ง่ายและหลีกเลี่ยง fatal error จาก constant ชื่อซ้ำ
