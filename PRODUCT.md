# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Students** (College and SHS) at AITE (Asian Institute of Technology and Education). They evaluate their teachers during an open evaluation period, mostly on their phones. They want to finish quickly and need to trust that their answers are anonymous.
- **Administrators**: `super_admin` (full management) and `dean` (monitoring and reports). They work mostly on desktop. They set up periods, programs, questionnaires, teachers and students, track completion while a period is open, and read and distribute the results.
- **Teachers** do not log in. They receive their evaluation reports by email.

## Product Purpose

Smart-Eval is AITE's web-based teacher evaluation system. It replaces manual or generic-form evaluations with a single secure flow: students evaluate the teachers they are actually enrolled with, admins watch participation live, and the results become reports that reach each teacher automatically. It succeeds when participation is high, students trust their answers are anonymous, and admins spend no manual effort assigning surveys or sending results.

## Positioning

What separates it from a generic survey tool (for example, Google Forms):

- **Automatic assignment:** each student is given the teachers to evaluate based on their course or program and year level. Nobody builds or sends survey links.
- **Anonymity guaranteed:** student answers cannot be traced back to an individual student by admins or teachers.
- **Live monitoring:** admins watch completion in real time during a period.
- **Report generation and automatic distribution:** reports are generated per teacher, faculty and period, and emailed to teachers automatically.
- **Secured system:** two-factor verification, trusted devices, remember-me, role-scoped admin access, and API security middleware.

## Operating Context

- **Evaluation periods** are opened and closed by admins. Students can evaluate only while a period is open, and see a "no evaluation available" state otherwise.
- **Two enrollment tracks:** College and SHS. Students pick their enrollment type before choosing teachers.
- **Admin areas:** dashboard, evaluation monitoring, evaluation periods, programs, questionnaires, teachers, students, report and analytics, archive, and backup.
- Reports are exported as PDF (dompdf/mPDF) and delivered by email (PHPMailer). Backups go to Google Drive.

## Capabilities and Constraints

- Stack: PHP (custom MVC: Controllers, Services, Repositories) on XAMPP/Apache with MySQL, Tailwind CSS v4 via the browser CDN build, vanilla JS, and Composer packages (PHPMailer, dompdf, mPDF, phpdotenv, Google API client).
- A Gemini service exists in `app/Services/AI/`, but AI-written insights were not confirmed as a product claim. Do not present them as a feature until confirmed.
- Smart-Eval is a capstone/thesis project, so the work will be presented and defended before a panel. Clarity, credibility, and a coherent, demonstrable system matter.

## Brand Commitments

- Product name: **Smart-Eval** (the login also uses "SmartEval"; spelling not yet standardized).
- Institution: **AITE**, Asian Institute of Technology and Education. Students are addressed as "AITEans" ("Welcome back, AITEans").
- Existing assets: `public/assets/images/aite-logo.png` / `.webp`, `school-img.jpeg`, `BG-login.png`.
- Existing voice on login: "Secure teacher evaluation, simplified."

## Evidence on Hand

- Real product copy and flows in `views/` (student, admin, auth) and email/operations docs in `docs/`.
- No testimonials, usage statistics, participation rates, or institutional endorsements have been provided. Do not invent any.

## Product Principles

1. **Trust before speed:** anonymity and security must be visible and true at every step, because students only answer honestly when they believe it.
2. **Zero manual routing:** the system decides who evaluates whom and who receives which report. The interface should never ask a person to do what the data already knows.
3. **Finish fast on a phone:** the student flow is a short, focused task done on mobile. Remove every extra step.
4. **Glanceable oversight:** admins need participation status and results at a glance, with detail one step away.
5. **Defensible clarity:** every screen should explain itself to a first-time viewer, such as a thesis panel, without narration.
