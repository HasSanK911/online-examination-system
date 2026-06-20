# University Online Examination System — Complete Functionality Document

> **Purpose of this document:** This is a comprehensive, factual description of every feature
> and functionality actually implemented in the University Online Examination System. It is
> written to serve as the source material for authoring a Final Year Project (FYP) thesis.
> Everything described below reflects the real, working codebase — not an aspirational plan.

---

## 1. Project Overview

The **University Online Examination System** (branded **UNIXAM**) is a web-based platform that
lets a university conduct, manage, grade, and analyse examinations entirely online. It replaces
the traditional pen-and-paper examination workflow with a secure, role-based digital system that
covers the full lifecycle of an exam — from creating a question bank, to scheduling the exam,
to a student attempting it under anti-cheating supervision, to automatic grading, manual
evaluation, result publication, ranking, and analytical reporting.

The system is built as a **single-page application (SPA)** experience using a modern Laravel +
Inertia.js + Vue 3 architecture, meaning the interface feels as fluid as a desktop application
while remaining a server-driven web app with strong security.

### 1.1 Core Objectives

- Digitise the entire examination process for a multi-faculty, multi-department university.
- Enforce examination integrity through server-side validation and client-side anti-cheating.
- Automatically grade objective questions and support teacher evaluation of subjective ones.
- Produce ranked results (class, department, semester) using real SQL window functions.
- Provide rich analytical dashboards (OLAP-style aggregations) for institutional decision-making.
- Generate downloadable PDF result cards and institutional reports.
- Maintain a complete audit trail of every significant action in the system.

### 1.2 Scale and Quality Targets

The system is designed with enterprise patterns (service layer, policies, queued jobs) so it can
scale toward thousands of concurrent students across multiple departments, while keeping the
codebase clean and maintainable.

---

## 2. Technology Stack (As Actually Used)

| Layer | Technology | Version |
|---|---|---|
| Backend framework | Laravel (PHP) | Laravel `^13.8`, PHP `^8.3` |
| Frontend SPA bridge | Inertia.js (Laravel + Vue adapter) | `^2.0` |
| Frontend framework | Vue 3 (Composition API) | `^3.4` |
| Styling | Tailwind CSS | `^4.3` |
| Charts | Chart.js (direct integration) | `^4.5` |
| Authentication scaffolding | Laravel Breeze (Inertia stack) | `^2.4` |
| Authorization / RBAC | Spatie Laravel Permission | `^8.0` |
| Activity / Audit logging | Spatie Laravel Activitylog | `^5.0` |
| PDF generation | barryvdh/laravel-dompdf | `^3.1` |
| Excel import/export | Maatwebsite Laravel Excel | `^3.1` |
| Social login support | Laravel Socialite | `^5.27` |
| API tokens | Laravel Sanctum | `^4.0` |
| Route helper (JS) | Tighten Ziggy | `^2.0` |
| Build tool | Vite | `^8.0` |
| Database | MySQL 8+ | — |
| Testing | PHPUnit | `^12.5` |
| Containerisation | Docker + Docker Compose | — |

**Brand identity:** The UI uses a burgundy/red brand colour (`#BC2739`) with a dark navy sidebar
(`#0f172a`), an "UNIXAM" wordmark, full dark-mode support, and a clean, card-based design system.

---

## 3. System Architecture

The application follows a layered, **separation-of-concerns** architecture:

- **Controllers** handle only HTTP concerns — they validate input, call a service, and return an
  Inertia page (or JSON for the exam API).
- **Service Layer** (`app/Services/`) contains all business logic. Six dedicated services own
  grading, attempt management, ranking, OLAP analytics, reporting, and auditing.
- **Models** (`app/Models/`) define the Eloquent relationships and casts for fourteen entities.
- **Policies** (`app/Policies/`) enforce per-model authorization rules.
- **Form Requests** (`app/Http/Requests/`) centralise validation rules.
- **Jobs** (`app/Jobs/`) handle asynchronous work such as auto-submitting expired exams.
- **Notifications** (`app/Notifications/`) deliver multi-channel (email + in-app) alerts.
- **Database Views** push heavy reporting joins into MySQL itself.
- **Middleware** shares authenticated user data with the frontend and enforces role access.

The frontend mirrors this with **role-scoped pages** organised under `resources/js/Pages/`
(Admin, Teacher, Student, Auth, Analytics, Reports) and a shared `AppLayout` that renders a
role-aware sidebar.

---

## 4. Database Design

The schema is implemented across ordered Laravel migrations and comprises the following core
tables. Foreign keys cascade on delete where appropriate, soft deletes protect master data, and
indexes are placed on every foreign key and frequently-filtered column.

### 4.1 Entities

- **users** — Core identity. Holds name, email, hashed password, email verification, a
  `role_type` enum (`super_admin`, `exam_controller`, `teacher`, `student`), Google ID and avatar
  (for social login), two-factor secret/recovery columns, `is_active` flag, and `last_login_at`.
- **faculties** — Top-level academic unit: name, unique code, dean name, contact info, status,
  soft-deletable.
- **departments** — Belongs to a faculty: name, unique code, head name, contact, status.
- **students** — Extends a user with academic profile: linked `user_id` and `department_id`,
  unique `student_id` (e.g. `CS-2021-001`), unique `roll_number`, semester (1–8), batch, guardian
  details, date of birth, gender, profile photo, enrollment date, and a status enum
  (`active`, `inactive`, `graduated`, `suspended`).
- **courses** — Belongs to a department: unique code, title, credit hours, semester, status.
- **course_teacher** (pivot) — Many-to-many assignment of teachers (users) to courses.
- **course_student** (pivot) — Many-to-many enrollment of students in courses.
- **question_banks** — A course-scoped collection of questions, owned by a teacher.
- **questions** — Belongs to a question bank. Carries a `type` enum
  (`mcq_single`, `mcq_multiple`, `true_false`, `fill_blank`, `short`, `descriptive`),
  HTML-rich question text, marks, difficulty (`easy`/`medium`/`hard`), JSON tags, optional image,
  explanation, and a `correct_answer` column (used by fill-in-the-blank).
- **question_options** — MCQ/true-false options with text, optional image, `is_correct` flag,
  and display order.
- **exams** — Belongs to a course and a creator (teacher). Holds title, description,
  instructions, total marks, passing marks, duration in minutes, start/end datetime, a `status`
  enum (`draft`, `scheduled`, `active`, `completed`, `cancelled`), and behaviour toggles:
  `shuffle_questions`, `shuffle_options`, `allow_backtrack`, `show_result_immediately`,
  `max_attempts`, and `published_at`.
- **exam_questions** (pivot) — Links questions into an exam with a per-exam display `order` and
  an optional `marks` override (so the same question can be worth different marks in different exams).
- **exam_attempts** — One student's sitting of an exam: timestamps (`started_at`, `submitted_at`),
  a `status` enum (`in_progress`, `submitted`, `auto_submitted`, `abandoned`), captured IP address
  and user agent, `tab_switch_count`, `suspicious_activity_count`, `time_spent_seconds`, and a JSON
  `question_order` (the shuffled order shown to that student).
- **attempt_answers** — One answer per question per attempt: JSON `selected_option_ids` (MCQ),
  `text_answer` (written types), `is_marked_for_review`, `is_answered`, and `saved_at`.
- **results** — One per attempt (unique). Holds total/obtained marks, percentage, grade, GPA,
  `is_pass`, the three computed ranks (`class_rank`, `department_rank`, `semester_rank`),
  `needs_evaluation`, `evaluated_at`, and `published_at`.
- **result_details** — Per-question breakdown of a result: obtained marks, max marks,
  correctness, teacher feedback, and the evaluating teacher's ID.
- **audit_logs** — A polymorphic audit trail: user, event name, auditable type/ID, old/new JSON
  values, IP address, user agent, and a description.

Spatie's standard tables (`roles`, `permissions`, `model_has_roles`, etc.) back the RBAC system,
and an `activity_log` table backs automatic model-change logging.

### 4.2 Database Views (Real SQL, not faked in PHP)

Three MySQL views are created in a dedicated migration and are read directly by the reporting and
OLAP services:

1. **`student_results_view`** — Joins results → attempts → students → users → departments →
   faculties → courses to produce a flat, report-ready row per published result (student code,
   name, department, faculty, course, exam, marks, percentage, grade, GPA, all three ranks).
2. **`course_performance_view`** — Aggregates per course: total results, average percentage,
   highest/lowest marks, pass count, fail count, and computed pass percentage.
3. **`department_performance_view`** — Aggregates per department: distinct student count, total
   exams, average percentage, total passed/failed, and pass rate.

All three filter on `published_at IS NOT NULL`, so only finalised results feed analytics.

---

## 5. User Roles and Access Control

Authorization is enforced through **Spatie Laravel Permission** (roles + granular permissions),
backed by route middleware and per-model Policies. There are four roles:

| Role | Responsibility |
|---|---|
| **Super Admin** | Full system control: manage faculties, departments, courses, students, view all exams, analytics, reports, and audit logs. |
| **Exam Controller** | Oversight role: monitor and schedule exams, publish results, view analytics and reports (no academic master-data management, no audit logs). |
| **Teacher** | Owns question banks and exams for assigned courses, evaluates subjective answers, views their own analytics. |
| **Student** | Attempts exams for enrolled courses, views own published results, downloads result cards. |

The seeder provisions **41 granular permissions** mapped onto these four roles, plus default user
accounts (a super admin, an exam controller, and a sample teacher) and a full set of sample
academic data (faculties, departments, courses, teachers, and 20 students across batches).

Four Policy classes enforce row-level rules, for example:

- A teacher can only edit an exam they created, and only while it is still a `draft`.
- A student can only view a result that belongs to them **and** has been published.
- Only super admins and exam controllers can publish results.

---

## 6. Functional Modules

### Module 1 — Authentication & Account Security

Built on **Laravel Breeze (Inertia stack)** with these capabilities:

- User registration with email verification.
- Login with rate limiting and "remember me".
- Forgot-password and reset-via-signed-link flow.
- Password confirmation gate for sensitive actions.
- Profile management — update profile information, change password, delete account.
- Database columns and Socialite integration prepared for **Google social login** and
  **two-factor authentication** (TOTP) — the schema and package support are in place.
- A `last_login_at` timestamp and `is_active` flag on every user.
- Secure session handling, CSRF protection on all state-changing routes, and bcrypt password
  hashing.

**Role-based landing:** After login, the root and `/dashboard` routes inspect the user's role and
redirect to the correct dashboard (admin, teacher, or student).

### Module 2 — Faculty Management (Super Admin)

Full CRUD for university faculties with search and pagination, status toggling (active/inactive),
soft deletes, and automatic activity logging of every change. Each faculty links to its
departments.

### Module 3 — Department Management (Super Admin)

CRUD for departments tied to faculties, with a faculty selector, unique code validation,
pagination, and the cascade relationship faculty → department → course.

### Module 4 — Course Management (Super Admin)

CRUD for courses linked to departments, with credit-hour and semester metadata, status control,
and — importantly — **teacher assignment**: super admins can attach or detach multiple teachers
to a course (the `course_teacher` pivot), which is what scopes a teacher's question banks and
exams.

### Module 5 — Student Management (Super Admin)

A drill-down student directory:

- The landing view shows **department cards** with summary statistics (total students, active
  students, departments, semesters).
- Selecting a department, then a semester, reveals the paginated student list for that cohort.
- Students are searchable by student ID, roll number, name, or email and filterable by status.
- Creating a student atomically creates both the linked **user account** (with the `student` role)
  and the **student profile** inside a database transaction.
- Each student has a detail page showing their courses and results.
- Students are soft-deletable, preserving historical data.

### Module 6 — Question Bank (Teacher)

Teachers build reusable, course-scoped question banks:

- Create question banks tied to courses they teach.
- A bank's detail page lists all its questions with type and difficulty badges.
- Add questions of **six types**:
  | Type | Auto-graded? |
  |---|---|
  | MCQ — single correct | Yes |
  | MCQ — multiple correct | Yes (all correct options required) |
  | True / False | Yes |
  | Fill in the blank | Yes (case-insensitive match) |
  | Short answer | No (teacher evaluates) |
  | Descriptive / essay | No (teacher evaluates) |
- Each question supports HTML-rich text, marks, difficulty, JSON tags, an optional image, and an
  explanation. MCQ and true/false questions store their options with a correctness flag; fill-in
  questions store an accepted answer string.
- The frontend provides search, type filters, and difficulty filters over the bank.

### Module 7 — Exam Management (Teacher)

The full exam lifecycle is implemented as a state machine:

```
draft → scheduled → active → completed
                  ↘ cancelled
```

Teachers can:

- **Create** an exam (starts in `draft`) for one of their courses, setting title, description,
  instructions, total and passing marks, duration (validated 5–300 minutes), and start/end times
  (validated so the end is after the start and both are in the future).
- Toggle exam behaviour: shuffle questions, shuffle options, allow/disallow backtracking, and
  show results immediately on submission.
- **Manage questions** via a two-panel interface: browse the question bank (with search, type,
  and difficulty filters) on one side and the assigned exam questions on the other. Questions are
  attached through the `exam_questions` pivot, given an order, and can have their marks overridden
  per exam.
- **Edit/delete** exams (only while in a permissible status — drafts can be edited; drafts and
  cancelled exams can be deleted).
- **Change status** — moving an exam to `scheduled` automatically dispatches an
  `ExamScheduledNotification` to all enrolled students.

Exam editing is locked once an exam leaves draft state, protecting integrity.

### Module 8 — Exam Attempt System (Student) — *The Critical Feature*

This is the heart of the system: a secure, full-screen, dark-themed exam environment built as a
dedicated Vue page. Its capabilities:

**Starting an attempt**
- A student can only start an exam that is genuinely **live** (status `active` and the current
  time within the start/end window) — validated server-side.
- The `ExamAttemptService` creates the attempt, records IP address and user agent, and generates
  the question order (shuffled if the exam enables it). It also pre-creates an answer row per
  question. If an in-progress attempt already exists, it is resumed rather than duplicated.

**Countdown timer**
- The remaining time is computed from the server-stored `started_at` plus the exam duration, so
  the clock cannot be reset by refreshing.
- It counts down live and changes colour as a warning: normal above five minutes, amber between
  one and five minutes, and a red pulse in the final minute.
- When the timer hits zero, the exam **auto-submits**.

**Question navigation**
- A sidebar grid of numbered buttons lets the student jump to any question.
- Each button is colour-coded: green (answered), amber (marked for review), grey (untouched),
  with the current question ring-highlighted.
- Previous/Next buttons move sequentially; the Previous button is disabled at the start when
  backtracking is turned off.
- Any question can be flagged "mark for review".
- The six question types each render an appropriate input (radio buttons, checkboxes, or
  text areas of varying sizes).

**Auto-save (resilience)**
- The current answer is auto-saved every 10 seconds.
- Selecting an MCQ option saves immediately; typing into a text answer saves on a 1.5-second
  debounce.
- Saves POST to the internal API endpoint `/api/exam/{attempt}/save-answer`, persisting the
  selected options, text, review flag, and answered state — so a dropped connection or browser
  crash never loses progress.

**Anti-cheating measures**
- **Tab-switch detection** via the `visibilitychange` event. Each switch increments a counter and
  is logged to `/api/exam/{attempt}/log-activity`. A warning banner appears after 5 switches, and
  after 10 the exam is auto-submitted (thresholds are configurable).
- **Copy / paste / cut blocking** on the exam surface.
- **Right-click (context menu) disabled.**
- **Keyboard shortcut blocking** for F12, Ctrl+U, Ctrl+S, Ctrl+I, Ctrl+J (developer tools and
  view-source).
- **Text selection disabled** and a distraction-free dark full-screen environment.
- Every suspicious event is recorded server-side in the audit trail, and the attempt's
  `suspicious_activity_count` and `tab_switch_count` are incremented.

**Submitting**
- A confirmation modal summarises how many questions were answered and how many are marked for
  review before final submission.
- On submit (manual, auto-on-timeout, or auto-on-cheating), the `ExamAttemptService` records the
  submission time, computes time spent, sets the correct status, and immediately triggers grading.
- If the exam is configured to show results immediately, the result is published on the spot;
  otherwise it waits for the controller/admin to publish.

An `AutoSubmitExamJob` (queued, with retries) provides server-side enforcement of the time limit
independent of the browser.

### Module 9 — Automatic Grading

The `GradingService` grades an attempt the moment it is submitted:

- It iterates every exam question and grades the four objective types automatically:
  - **MCQ single / true-false:** full marks only if the selected option matches the correct one.
  - **MCQ multiple:** full marks only if the set of selected options exactly equals the set of
    correct options (all-or-nothing; partial credit is a configurable option).
  - **Fill in the blank:** full marks on a case-insensitive, trimmed string match.
- Subjective types (short, descriptive) are recorded with zero marks and flagged
  `needs_evaluation` for the teacher.
- It writes a `result_detail` row per question (obtained vs. max marks, correctness).
- It computes the overall percentage, looks up the grade and GPA from the configurable grade
  scale, sets the pass/fail flag against the passing threshold, and saves the `result`.

**Grade scale** (from `config/examination.php`):

| Percentage | Grade | GPA |
|---|---|---|
| 90–100 | A+ | 4.0 |
| 85–89 | A | 4.0 |
| 80–84 | B+ | 3.5 |
| 75–79 | B | 3.0 |
| 70–74 | C+ | 2.5 |
| 65–69 | C | 2.0 |
| 60–64 | D | 1.0 |
| below 60 | F | 0.0 |

### Module 10 — Manual Evaluation (Teacher)

For short and descriptive answers, the teacher has a dedicated evaluation workflow:

- An evaluation list shows every submitted/auto-submitted attempt for an exam, how many
  manually-graded questions are still pending, and the current result status.
- Opening an attempt presents each subjective answer with a marks input and a feedback textarea.
- On submission, the system saves the awarded marks and feedback into `result_details` (recording
  which teacher evaluated each), then **recalculates** the total marks (auto + manual),
  percentage, grade, and GPA, clears the `needs_evaluation` flag, and **re-computes the exam
  rankings**.

### Module 11 — Result Management & Publication

- Results exist in an unpublished state until an admin or exam controller publishes them.
- **Publishing** an exam's results sets `published_at`, sends a `ResultPublishedNotification`
  (email + in-app) to each affected student, computes all three ranks, and writes an audit log.
- Students see only their own published results in a clean list (exam, course, score, grade,
  rank, pass/fail, date).
- A result detail page shows a large grade/score summary, GPA, pass/fail status, class rank, and
  a per-question breakdown table (correct rows tinted green, incorrect tinted red).

### Module 12 — Ranking System (Real SQL Window Functions)

The `RankingService` computes rankings using genuine MySQL window functions (not PHP loops):

- **Class rank** — `RANK() OVER (PARTITION BY exam_id ORDER BY obtained_marks DESC)`.
- **Department rank** — partitioned by the student's department.
- **Semester rank** — partitioned by the student's semester.
- It can also return ranking tables that simultaneously expose `ROW_NUMBER()`, `RANK()`, and
  `DENSE_RANK()` for comparison, a **top-N students per course** query using `DENSE_RANK()` over a
  course partition, and an aggregated department ranking based on each student's average
  percentage.

Ranks are recomputed whenever results are published or a manual evaluation changes a score, and
are stored back onto the `results` rows for fast display.

### Module 13 — OLAP Analytics Dashboard (Admin / Controller)

The `OlapService` powers an analytical dashboard with real aggregation queries:

- **Overview statistics** — distinct students examined, total results, overall average, pass rate,
  highest and lowest scores.
- **Monthly performance trend** — the last 12 months of average score and pass percentage,
  rendered as a multi-line Chart.js chart.
- **Grade distribution** — counts per grade with each grade's share of the total computed via a
  `SUM(COUNT(*)) OVER()` window function, rendered as a colour-coded bar chart.
- **Department performance** — read from `department_performance_view`, shown as a table with a
  colour-graded pass-rate progress bar.
- **Course performance** — read from `course_performance_view`.
- **Semester performance matrix** — a department-by-semester cross-tab that ranks departments
  within each semester using a window function.
- **Top students per course** — a ranked table with medal indicators for the top three.

### Module 14 — Reporting System (PDF)

The `ReportService` plus DomPDF produce four downloadable PDF reports, each with its own Blade
template under `resources/views/pdf/reports/`:

- **Student report** — a full academic transcript: every exam, grades, GPA, rankings, and a
  summary (total exams, passed, failed, CGPA, average percentage).
- **Exam report** — a complete class result sheet with every student's marks, grade, pass/fail,
  rank, and exam-level summary statistics (average, highest, lowest).
- **Department report** — aggregate performance across all students and exams in a department.
- **Course report** — aggregate performance for a course.

Students additionally download an individual **result card** PDF (`pdf/result-card.blade.php`)
for any published result.

### Module 15 — Notification System

Three queued notifications, each delivered over both **email** and the **in-app database channel**:

- `ExamScheduledNotification` — when an exam is moved to `scheduled`, enrolled students are told.
- `ExamReminderNotification` — a reminder that an exam starts soon (email + in-app payload).
- `ResultPublishedNotification` — when results are published, each student receives their grade,
  GPA, and pass/fail outcome with a link to the result.

### Module 16 — Audit Log System

Two complementary mechanisms record system activity:

1. **Automatic model logging** via Spatie Activitylog on the master-data models (faculties,
   departments, courses, students, exams) — every create/update/delete is captured with before
   and after values.
2. **Explicit audit logging** via the `AuditService`, which records security- and
   integrity-relevant events — `exam_start`, `exam_submit`, `exam_auto_submitted`,
   `suspicious_activity`, and `result_publish` — together with the acting user, IP address, user
   agent, and a description.

Super admins view the audit trail through a paginated, filterable interface (by event, user, date
range, and IP).

---

## 7. Dashboards

Each role has a tailored dashboard:

- **Admin dashboard** — six headline stat cards (students, exams, courses, departments, published
  results, users), an upcoming-exams timeline, quick-navigation tiles, a pending-results alert,
  and a live recent-activity feed sourced from the audit log.
- **Teacher dashboard** — stat cards for courses, students, exams, and published results; a
  list of assigned courses with student counts; recent exams with status badges; and a
  pending-evaluations banner linking straight to the evaluation queue.
- **Student dashboard** — stat cards for exams taken, passed, average score, and class rank; an
  upcoming-exams widget with live/scheduled badges; and a recent-results widget with grade
  colour-coding.

---

## 8. Internal Exam API

A small set of authenticated JSON endpoints (restricted to students) backs the real-time exam
experience, separate from the page routes:

- `POST /api/exam/{attempt}/save-answer` — persist a single answer (called continuously during the
  attempt).
- `POST /api/exam/{attempt}/submit` — finalise and grade the attempt.
- `POST /api/exam/{attempt}/log-activity` — record an anti-cheating event.

These are deliberately lightweight and idempotent so the exam UI can call them frequently and
recover gracefully from network interruptions.

---

## 9. Security Summary

- **Role-based access control** on every route via Spatie middleware, reinforced by per-model
  Policies for row-level rules.
- **SQL-injection safe** — all queries use Eloquent or parameterised bindings, including the raw
  window-function ranking queries.
- **XSS protection** through Vue and Blade auto-escaping.
- **CSRF protection** on all state-changing requests.
- **Rate limiting** on authentication and verification routes.
- **bcrypt** password hashing.
- **Exam integrity** — the timer, the live-window check, and the submission flow are all validated
  server-side; the client cannot fabricate marks because grading happens entirely on the server
  against the stored correct answers.
- **Full audit trail** of authentication, exam, and result events with captured IP and user agent.

---

## 10. Configuration & Deployment

- **`config/examination.php`** centralises tunable rules: the grade scale, passing percentage
  (50%), auto-save interval (10 s), tab-switch warning threshold (5) and auto-submit threshold
  (10), MCQ partial-credit toggle, result-card QR toggle, and exam duration limits.
- **Docker Compose** defines the deployment stack (PHP-FPM app container, Nginx, MySQL, Redis,
  Mailpit for local mail, and a queue worker).
- **Queues** (database driver by default, upgradeable to Redis) handle asynchronous work such as
  notifications and exam auto-submission.

---

## 11. Frontend Experience Summary

- A single **AppLayout** renders a role-aware sidebar that shows only the navigation each role is
  permitted to use, a sticky top bar, flash messages, and a persisted **dark-mode** toggle.
- Roughly **49 Vue components** (around 35 pages and 14 shared components) deliver the interface,
  built almost entirely with Vue 3 and Tailwind for a lightweight, maintainable codebase.
- Data flows from Laravel to Vue through **Inertia props** — there is no separate REST API to
  maintain for page data, which keeps the frontend and backend tightly and safely in sync.
- Reusable components include stat cards, modals, data tables, form inputs with validation
  display, and the exam-specific timer and question navigator.
- **Chart.js** renders the analytics line and bar charts directly on canvas elements.

---

## 12. What Makes This Project Distinction-Worthy

- **Real database engineering** — three SQL views and multiple window-function queries
  (`RANK`, `DENSE_RANK`, `ROW_NUMBER`, windowed `SUM`) implemented in actual SQL rather than
  emulated in PHP, demonstrating genuine OLAP and analytical database skills.
- **A production-grade, security-hardened exam attempt engine** — the most difficult part of any
  online examination system — complete with server-authoritative timing, resilient auto-save, and
  a layered anti-cheating system.
- **Clean enterprise architecture** — a strict service layer, policies, form requests, queued
  jobs, multi-channel notifications, and a dual audit trail.
- **Complete role-based workflows** for four distinct user types, each with its own dashboard,
  navigation, and permissions.
- **End-to-end coverage** of the examination lifecycle — from question authoring through to
  ranked, published results, PDF transcripts, and institutional analytics.

---

*This document describes the implemented functionality of the University Online Examination
System (UNIXAM) and is intended as source material for the project thesis.*
