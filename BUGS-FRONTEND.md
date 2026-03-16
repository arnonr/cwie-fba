# BUGS-FRONTEND.md — CWIE-FBA Frontend Issues

> Last updated: 2026-03-16

---

## Critical

- [ ] **F-S5** — Sanitize `news_detail` with DOMPurify before rendering via `v-html` — currently allows stored XSS (`resources/js/pages/dashboards/new/[id].vue:48`, `resources/js/pages/staff/news/view/[id].vue:139`)
  > เนื้อหาข่าว (`news_detail`) ที่มาจากฐานข้อมูลถูก render ด้วย `v-html` โดยตรงโดยไม่มีการ sanitize ผู้ที่สามารถสร้างหรือแก้ไขข่าวได้สามารถฝัง `<script>` ที่รันใน browser ของผู้อ่านทุกคน ทำให้ขโมย token, redirect ผู้ใช้ หรือกระทำการในนามผู้ใช้ได้ ควรติดตั้ง DOMPurify และ sanitize ก่อน render ทุกครั้ง

- [ ] **F-S6** — Move access token to `httpOnly` cookie instead of `localStorage` to prevent XSS token theft (`resources/js/pages/login.vue:130-135`)
  > Access token และข้อมูลผู้ใช้ถูกเก็บไว้ใน `localStorage` ซึ่ง JavaScript บนหน้าเว็บสามารถอ่านได้ทั้งหมด หากมีช่องโหว่ XSS (เช่น F-S5) ผู้โจมตีสามารถขโมย token ไปได้ทันที ควรเปลี่ยนไปใช้ `httpOnly` cookie ที่ JavaScript เข้าถึงไม่ได้

---

## High

- [ ] **F-Q4** — CASL abilities are built entirely client-side from `account_type` — any user can modify `localStorage` to gain admin abilities; requires server-side authorization enforcement on all sensitive actions (`resources/js/pages/login.vue:73-128`)
  > สิทธิ์การใช้งาน (CASL abilities) ถูกสร้างฝั่ง frontend ล้วน ๆ โดยอิงจาก `account_type` ที่เก็บใน `localStorage` ผู้ใช้สามารถเปิด DevTools แก้ไข `localStorage` แล้ว reload เพื่อให้ตัวเองมีสิทธิ์ admin ได้ทันที CASL ควรใช้เป็นแค่ UI helper เท่านั้น ต้องมีการตรวจสอบสิทธิ์จริงที่ฝั่ง backend ด้วย

---

## Medium

- [ ] **F-S10** — Fix open redirect after login — validate `route.query.to` starts with `"/"` before redirecting to prevent phishing redirects (`resources/js/pages/login.vue:138`)
  > หลัง login สำเร็จ ระบบจะ redirect ไปยัง URL ที่ระบุใน query parameter `?to=...` โดยไม่ตรวจสอบว่าเป็น URL ภายในหรือไม่ ผู้โจมตีสามารถสร้างลิงก์เช่น `/login?to=https://evil.com` เพื่อหลอกให้ผู้ใช้ที่เพิ่ง login เสร็จถูก redirect ไปยังเว็บปลอมได้ ควรตรวจสอบว่า `to` ขึ้นต้นด้วย `"/"` เท่านั้น

---

## Low / Quality

- [ ] **F-Q1** — Error responses from the API return HTTP `200` for failed logins, making it impossible to distinguish success from failure by status code alone — align with backend fix B-B4
  > เมื่อ login ไม่สำเร็จ API คืน HTTP `200` พร้อม `{"message": "Unauthorized"}` ทำให้ frontend ต้องตรวจสอบ body แทน status code ซึ่งเสี่ยงต่อการพลาด error handling ควรแก้คู่กับ B-B4 ให้คืน `401` แทน
