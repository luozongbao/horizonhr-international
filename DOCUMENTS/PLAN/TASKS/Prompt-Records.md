ช่วย Review Requirements ใน DOCUMENTS/REQUIREMENTS-EN.md เป็นหลัก และตรวจสอบความถูกต้องและครบถ้วนขอเอกสารอื่น ๆ เช่น  
DOCUMENTS/DESIGNS/API_DOCUMENTATION.md
DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md
DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md
DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md
นอกจากนี้เรามี Design documents ใน DOCUMENTS/DESIGNS/ ให้ตรวจสอบควาามถูกต้องครบถ้วนของ เอกสาร DESIGNS

ถ้าส่วนไหนไม่ครบไม่เป็นไปตาม Requirements ช่วยปรับให้ถูกต้องด้วย และทำเอกสารสรุปส่ิ่งที่ปรับเปลี่ยนแก้ไขหรือเพิ่มเติมใน DOCUMENTS/CHANGES.md ด้วยครับ


---

ผมไม่กำหนด Frontend Techsstach เป็น HTML5 + CSS3 + JavaScript แล้ว ให้คุณแนะนำ Frontend tech stack และอัพเดต Documents หน่อยครับ

---

จากเนื้อหาของโปรเจค ช่วยวางแผนการพัฒนา โปรเจคนี้ใน DOCUMENTS/PLAN/PLAN.md ให้หน่อยครับ เขียน Issue TASK DOCUMENTS สำหรับ AI ให้เข้ามาทำการ Implement/Development โปรเจคนี้จนสำเสร็จได้ใน DOCUMENTS/PLAN/TASKS/###-[TASK].md แต่ละ Issue Task Document ควรที่จะ
- ชัดเจนทั้ง Description, Reference, Core Feature, Acceptance Criteria
- นำไปสู่ผลลัพธ์ความสำเร็จของโปรเจคที่เป็นไปตาม REQUIREMENTS และเอกสาร DESIGN ต่าง ๆ
- มีเอกสารอ้างอิงให้ AI สามารถตรวจสอบและทำความเข้าใจโปรเจคได้ 
- เรียงลำดับเรื่องของ การทำงานลำดับก่อนหลังให้ชัดเจนและถูกต้องด้วย
- 1 ใบงาน (TASK) AI ควรให้ AI สามารถดำเนินการได้ตั้งแต่ต้นจนจบโดยไม่ Context Overflow 

เนื้อหาของการสร้างโปรเจคนี้รวมตั้งแต่การตรวจสอบเอกสาร การทำ Design การตรวจสอบและแก้ไข Visual Mockup ที่มีอยู่ จนนำมาใช้จริงและเห็นผลจริง, การเซ็ตอัพโปรเจค, เซ็ตอัพ Database และ migration, เขียนโค้ด Backend, Frontend, integration แต่ละหน้า, การ Reseach API documents ของระบบ ที่จะทำการ Integrate, และการดำเนินการ integration ระบบ (ปัจจุบันเรามีการต้อง Integration TRTC มีเอกสารที่ Research ไว้แล้วเบื้องตนใน DOCUMENTS/TRTC_Integration.md)

เมื่อ Implement ครบทุก Issue Task Documents แล้ว Project ควรที่จะ บริบูรณ์สามารถใช้งานได้เลย

---

จากเนื้อหาที่ Plan ออกมาผมเห็นว่า มีเกี่ยวกับ Aliyun WebRTC, เราเปลี่ยนจากการใช้ Aliyun เป็นของ Tencent (TRTC) ผมอยากทราบว่า ถ้าทำไปก่อนแล้วค่อยมาเปลี่ยนการ Integration ส่วนนี้ส่วนเดียว กับ เปลี่ยนเนื้อหาใน Issue Task ตอนนี้เลยแบบไหนมีประสิทธิภาพและดีกว่ากันครับ?

---

โอเคครับ งั้นจากเนื้อหาที่ Plan ออกมาผมเห็นว่า มีเกี่ยวกับ Aliyun WebRTC, เราเปลี่ยนจากการใช้ Aliyun เป็นของ Tencent (TRTC) ช่วยอัพเดตเอกสารให้เป็นไปตามนี้ด้วย รวมทั้ง REQUIREMENTS.md System DESIGN, PLAN, Issue Task ให้สอดคล้องตามนี้ด้วยครับ อย่าลืมเรามีเอกสาร TRTC_Integration.md ไว้ให้แล้วเบื้องต้น reference ให้มีประโยชน์ให้ได้มากที่สุดเลยครับ

--- 

ก่อนจะเริ่ม Implement ช่วยแจ้ง Prompt ที่ผมควรใช้ในการ Implement แต่ละ Issue Task หน่อยครับ หรือใช้ คำว่า "Implement Task 001" แค่นี้ก็พอครับ? มีตรงไหนอะไรจะแนะนำเพิ่มเติมไหมครับ?

--- 

Implement DOCUMENTS/PLAN/TASKS/001-VISUAL-MOCKUP-REVIEW.md

---

Implement DOCUMENTS/PLAN/TASKS/002-PROJECT-INFRASTRUCTURE.md

---

Implement DOCUMENTS/PLAN/TASKS/003-DATABASE-MIGRATIONS.md

---

Implement DOCUMENTS/PLAN/TASKS/004-BACKEND-AUTH.md

---

Implement DOCUMENTS/PLAN/TASKS/005-BACKEND-SOCIAL-OAUTH.md

--- 

Implement DOCUMENTS/PLAN/TASKS/006-BACKEND-USER-ADMIN.md

---

Implement DOCUMENTS/PLAN/TASKS/007-BACKEND-CMS.md

---

Implement DOCUMENTS/PLAN/TASKS/008-BACKEND-SETTINGS.md

---

ก่อนไปต่ออยากให้ช่วยเช็คความเรียบร้อย ความสมบูรณ์ ถูกต้อง ครบถ้วนของการทำงานตามใบงาน ตั้งแต่ Task 001-009 ครับ ครบถ้วนขาดหรือมีอะไรต้องเพิ่มเติมให้สรุปเป็นเอกสาร DOCUMENTS/TEST/TASK-AUDIT/RESULT-001-009.md และแนะนำ AI Prompt ที่จะใช้สั่ง AI ให้ ใบงานตั้งแต่ 001-009 ครบถ้วนบริบูรณ์ด้วยครับ

---

Implement DOCUMENTS/PLAN/TASKS/010-BACKEND-STUDENT.md

---

Implement DOCUMENTS/PLAN/TASKS/011-BACKEND-ENTERPRISE.md

---

Implement DOCUMENTS/PLAN/TASKS/012-BACKEND-APPLICATIONS.md

---

Implement DOCUMENTS/PLAN/TASKS/013-BACKEND-INTERVIEWS.md

---

Implement DOCUMENTS/PLAN/TASKS/014-BACKEND-SEMINARS.md

---

Implement DOCUMENTS/PLAN/TASKS/015-BACKEND-UNIVERSITY-CONTACT-STATS.md

---

ก่อนไปต่ออยากให้ช่วยเช็คความเรียบร้อย ความสมบูรณ์ ถูกต้อง ครบถ้วนของการทำงานตามใบงาน BACKEND ครับ ครบถ้วนขาดหรือมีอะไรต้องเพิ่มเติมให้สรุปเป็นเอกสาร DOCUMENTS/TEST/TASK-AUDIT/BACKEND-AUDIT-RESULT.md และแนะนำ AI Prompt ที่จะใช้สั่ง AI ให้ งาน BACKEND ครบถ้วนบริบูรณ์ด้วยครับ

---

Implement DOCUMENTS/PLAN/TASKS/016-OSS-STORAGE.md

---

Implement DOCUMENTS/PLAN/TASKS/017-EMAIL-SERVICE.md

---

Implement DOCUMENTS/PLAN/TASKS/018-TRTC-BACKEND.md

---

Implement DOCUMENTS/PLAN/TASKS/019-TRTC-LIVE-BACKEND.md

---

Implement DOCUMENTS/PLAN/TASKS/020-FRONTEND-BOOTSTRAP.md

---

Implement DOCUMENTS/PLAN/TASKS/021-FRONTEND-LAYOUT-STORES.md

---

Implement DOCUMENTS/PLAN/TASKS/022-FRONTEND-AUTH-PAGES.md

---

Implement DOCUMENTS/PLAN/TASKS/023-FRONTEND-HOME.md

---

Implement DOCUMENTS/PLAN/TASKS/024-FRONTEND-ABOUT-STUDY.md

---

Implement DOCUMENTS/PLAN/TASKS/025-FRONTEND-TALENT-CORPORATE.md

---

Learn this project Requirements in DOCUMENTS/REQUIREMENTS-EN.md and its Designs in DOCUMENTS/DESIGNS to understand the over view of the project then continue to Implement DOCUMENTS/PLAN/TASKS/026-FRONTEND-NEWS-CONTACT.md

---

Implement DOCUMENTS/PLAN/TASKS/027-FRONTEND-SEMINAR-PUBLIC.md

---

Implement DOCUMENTS/PLAN/TASKS/028-FRONTEND-STUDENT-DASHBOARD-PROFILE.md

---

Implement DOCUMENTS/PLAN/TASKS/029-FRONTEND-STUDENT-JOBS-APPLICATIONS.md

---

Implement DOCUMENTS/PLAN/TASKS/030-FRONTEND-STUDENT-INTERVIEWS.md

---

Implement DOCUMENTS/PLAN/TASKS/031-FRONTEND-STUDENT-SEMINARS.md

---

Implement DOCUMENTS/PLAN/TASKS/032-FRONTEND-ENTERPRISE-DASHBOARD-JOBS.md

---

Implement DOCUMENTS/PLAN/TASKS/033-FRONTEND-ENTERPRISE-TALENT-INTERVIEWS.md

---

Implement DOCUMENTS/PLAN/TASKS/034-FRONTEND-ADMIN-DASHBOARD-USERS.md

---

Implement DOCUMENTS/PLAN/TASKS/035-FRONTEND-ADMIN-RESUMES.md

---

Implement DOCUMENTS/PLAN/TASKS/036-FRONTEND-ADMIN-INTERVIEWS-SEMINARS.md

---

Implement DOCUMENTS/PLAN/TASKS/037-FRONTEND-ADMIN-CMS.md

---

Implement DOCUMENTS/PLAN/TASKS/038-FRONTEND-ADMIN-SETTINGS-LANGUAGE.md