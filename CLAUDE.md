# University Online Examination System — CLAUDE.md

## Project Identity

**Name:** University Online Examination System
**Stack:** Laravel 13 · PHP 8.3+ · MySQL 8+ · Inertia.js · Vue 3 · Tailwind CSS v4 · ShadCN Vue
**Purpose:** Enterprise-grade web platform for conducting and managing university examinations
**Scale Target:** 10,000+ concurrent students, multiple departments, production-ready
**FYP Grade Target:** Distinction

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 13 (PHP 8.3+) |
| Frontend SPA | Inertia.js + Vue 3 (Composition API) |
| Styling | Tailwind CSS v4 |
| UI Components | ShadCN Vue + Heroicons |
| Authentication | Laravel Breeze (Inertia stack) |
| Authorization | Spatie Laravel Permission |
| Database | MySQL 8+ |
| Queue Worker | Laravel Queues (database driver, upgradeable to Redis) |
| Notifications | Laravel Notifications (mail + database channels) |
| Excel Import/Export | Laravel Excel (Maatwebsite) |
| PDF Generation | DomPDF (barryvdh/laravel-dompdf) |
| Charts | Chart.js via vue-chartjs |
| Rich Text Editor | TipTap or Quill |
| File Storage | Laravel Storage (local / S3-compatible) |
| Testing | PHPUnit 12 |
| Dev Tooling | Vite 8, Concurrently, Laravel Pint, Laravel Pail |
| Containerization | Docker + Docker Compose |

---

## Installed Packages (to be added via composer/npm)

### PHP (composer require)
```
laravel/breeze
spatie/laravel-permission
maatwebsite/excel
barryvdh/laravel-dompdf
laravel/socialite
spatie/laravel-activitylog
```

### JS (npm install)
```
@inertiajs/vue3
vue
@vitejs/plugin-vue
vue-chartjs
chart.js
@headlessui/vue
@heroicons/vue
radix-vue
class-variance-authority
clsx
tailwind-merge
```

---

## Architecture Patterns

- **Service Layer Pattern** — all business logic lives in `app/Services/`, never in controllers
- **Repository Pattern** — database queries abstracted into `app/Repositories/`
- **Form Request Validation** — all input validated via dedicated `app/Http/Requests/` classes
- **Resource Classes** — API/Inertia responses shaped via `app/Http/Resources/`
- **Policies** — authorization for every model in `app/Policies/`
- **Events & Listeners** — side effects (notifications, logs) decoupled via `app/Events/` + `app/Listeners/`
- **Jobs** — async processing (bulk import, PDF generation, email dispatch) in `app/Jobs/`
- **SOLID Principles** — single responsibility, open/closed, dependency injection throughout

---

## Directory Structure

```
app/
├── Console/Commands/          # Artisan commands (exam reminders, cleanup)
├── Events/                    # ExamScheduled, ResultPublished, ExamSubmitted, etc.
├── Exceptions/                # Custom exception handlers
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Breeze auth controllers
│   │   ├── Admin/             # SuperAdmin controllers
│   │   ├── Teacher/           # Teacher-scoped controllers
│   │   ├── Student/           # Student-scoped controllers
│   │   └── ExamController/    # Exam controller role controllers
│   ├── Middleware/
│   │   ├── EnsureEmailVerified.php
│   │   ├── CheckExamAccess.php
│   │   ├── PreventCheating.php
│   │   └── RoleMiddleware.php
│   └── Requests/              # Form Request classes per feature
├── Jobs/
│   ├── ImportStudentsJob.php
│   ├── GenerateResultPdfJob.php
│   ├── SendExamReminderJob.php
│   └── AutoSubmitExamJob.php
├── Listeners/
├── Models/
│   ├── User.php
│   ├── Student.php
│   ├── Faculty.php
│   ├── Department.php
│   ├── Course.php
│   ├── QuestionBank.php
│   ├── Question.php
│   ├── QuestionOption.php
│   ├── Exam.php
│   ├── ExamQuestion.php
│   ├── ExamAttempt.php
│   ├── AttemptAnswer.php
│   ├── Result.php
│   ├── ResultDetail.php
│   ├── Notification.php
│   └── AuditLog.php
├── Notifications/
│   ├── ExamScheduledNotification.php
│   ├── ExamReminderNotification.php
│   └── ResultPublishedNotification.php
├── Policies/
├── Repositories/
│   ├── Contracts/             # Repository interfaces
│   └── Eloquent/              # Eloquent implementations
├── Services/
│   ├── AuthService.php
│   ├── StudentService.php
│   ├── ExamService.php
│   ├── GradingService.php
│   ├── RankingService.php
│   ├── ResultService.php
│   ├── OlapService.php
│   ├── ReportService.php
│   └── AuditService.php
└── Providers/
    ├── AppServiceProvider.php
    └── AuthServiceProvider.php

database/
├── migrations/                # One file per table, ordered
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RolePermissionSeeder.php
│   ├── FacultySeeder.php
│   ├── DepartmentSeeder.php
│   └── AdminUserSeeder.php
└── factories/

resources/
├── js/
│   ├── app.js                 # Inertia bootstrap
│   ├── Components/
│   │   ├── ui/                # ShadCN base components (Button, Card, Table, etc.)
│   │   ├── App/               # App-specific shared components
│   │   │   ├── AppLayout.vue
│   │   │   ├── Sidebar.vue
│   │   │   ├── Navbar.vue
│   │   │   ├── DataTable.vue
│   │   │   ├── StatsCard.vue
│   │   │   └── ChartWrapper.vue
│   │   └── Exam/
│   │       ├── ExamTimer.vue
│   │       ├── QuestionNavigator.vue
│   │       ├── QuestionCard.vue
│   │       └── ExamProgressBar.vue
│   └── Pages/
│       ├── Auth/              # Login, Register, ForgotPassword, etc.
│       ├── Dashboard/         # Role-specific dashboards
│       ├── Faculties/
│       ├── Departments/
│       ├── Students/
│       ├── Courses/
│       ├── Questions/
│       ├── Exams/
│       ├── Attempt/           # Live exam taking UI
│       ├── Results/
│       ├── Analytics/
│       └── Reports/
└── views/
    └── app.blade.php          # Inertia root template

routes/
├── web.php
├── auth.php
└── api.php                    # Internal JSON endpoints for exam auto-save

tests/
├── Feature/
│   ├── AuthTest.php
│   ├── ExamAttemptTest.php
│   ├── GradingTest.php
│   ├── ResultTest.php
│   └── RankingTest.php
└── Unit/
    ├── GradingServiceTest.php
    ├── RankingServiceTest.php
    └── OlapServiceTest.php
```

---

## Database Schema

### Core Tables

#### users
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar(255) | |
| email | varchar(255) | unique |
| password | varchar(255) | bcrypt |
| email_verified_at | timestamp | nullable |
| two_factor_secret | text | nullable |
| two_factor_recovery_codes | text | nullable |
| remember_token | varchar(100) | nullable |
| google_id | varchar(255) | nullable, Social Login |
| is_active | boolean | default true |
| last_login_at | timestamp | nullable |
| timestamps | | |

#### students
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | → users |
| department_id | bigint FK | → departments |
| student_id | varchar(50) | unique (e.g. CS-2021-001) |
| roll_number | varchar(50) | unique |
| semester | tinyint | 1–8 |
| batch | varchar(20) | e.g. 2021–2025 |
| phone | varchar(20) | nullable |
| address | text | nullable |
| status | enum | active, inactive, graduated |
| timestamps | | |

#### faculties
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar(255) | |
| code | varchar(20) | unique |
| dean_name | varchar(255) | nullable |
| status | enum | active, inactive |
| timestamps | | |

#### departments
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| faculty_id | bigint FK | → faculties |
| name | varchar(255) | |
| code | varchar(20) | unique |
| description | text | nullable |
| timestamps | | |

#### courses
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| department_id | bigint FK | → departments |
| code | varchar(30) | unique |
| title | varchar(255) | |
| credit_hours | tinyint | |
| semester | tinyint | |
| status | enum | active, inactive |
| timestamps | | |

#### course_teacher (pivot)
| Column | Type |
|---|---|
| course_id | bigint FK |
| user_id | bigint FK (teacher) | |

#### course_student (pivot)
| Column | Type |
|---|---|
| course_id | bigint FK |
| student_id | bigint FK |

#### question_banks
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| course_id | bigint FK | |
| user_id | bigint FK | creator (teacher) |
| title | varchar(255) | |
| description | text | nullable |
| timestamps | | |

#### questions
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| question_bank_id | bigint FK | |
| type | enum | mcq_single, mcq_multiple, true_false, fill_blank, short, descriptive |
| question_text | longtext | supports HTML (rich text) |
| marks | decimal(5,2) | |
| difficulty | enum | easy, medium, hard |
| tags | json | array of tag strings |
| image_path | varchar(500) | nullable |
| explanation | text | nullable |
| is_active | boolean | default true |
| timestamps | | |

#### question_options
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| question_id | bigint FK | |
| option_text | text | |
| is_correct | boolean | |
| order | tinyint | display order |

#### exams
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| course_id | bigint FK | |
| created_by | bigint FK | → users (teacher) |
| title | varchar(255) | |
| description | text | nullable |
| total_marks | decimal(8,2) | |
| passing_marks | decimal(8,2) | |
| duration_minutes | int | |
| start_time | datetime | |
| end_time | datetime | |
| status | enum | draft, scheduled, active, completed, cancelled |
| instructions | text | nullable |
| allow_backtrack | boolean | default true |
| shuffle_questions | boolean | default false |
| shuffle_options | boolean | default false |
| show_result_immediately | boolean | default false |
| timestamps | | |

#### exam_questions (pivot with order/marks override)
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| exam_id | bigint FK | |
| question_id | bigint FK | |
| order | int | display order |
| marks | decimal(5,2) | can override question default |

#### exam_attempts
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| exam_id | bigint FK | |
| student_id | bigint FK | |
| started_at | datetime | |
| submitted_at | datetime | nullable |
| status | enum | in_progress, submitted, auto_submitted, abandoned |
| ip_address | varchar(45) | |
| user_agent | text | |
| tab_switch_count | int | default 0 |
| suspicious_activity_count | int | default 0 |
| time_spent_seconds | int | nullable |
| timestamps | | |

#### attempt_answers
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| attempt_id | bigint FK | |
| question_id | bigint FK | |
| selected_option_ids | json | for MCQ |
| text_answer | longtext | for fill/short/descriptive |
| is_marked_for_review | boolean | default false |
| is_answered | boolean | default false |
| saved_at | timestamp | last auto-save |

#### results
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| attempt_id | bigint FK | unique |
| student_id | bigint FK | |
| exam_id | bigint FK | |
| total_marks | decimal(8,2) | |
| obtained_marks | decimal(8,2) | |
| percentage | decimal(5,2) | |
| grade | varchar(5) | A+, A, B+, B, C, D, F |
| gpa | decimal(3,2) | |
| is_pass | boolean | |
| class_rank | int | nullable, computed |
| department_rank | int | nullable, computed |
| evaluated_at | timestamp | nullable |
| published_at | timestamp | nullable |
| timestamps | | |

#### result_details
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| result_id | bigint FK | |
| question_id | bigint FK | |
| obtained_marks | decimal(5,2) | |
| is_correct | boolean | nullable |
| teacher_feedback | text | nullable (for descriptive) |
| evaluated_by | bigint FK | nullable → users |

#### audit_logs
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | nullable |
| event | varchar(100) | login, exam_start, exam_submit, etc. |
| auditable_type | varchar(255) | morphable |
| auditable_id | bigint | morphable |
| old_values | json | nullable |
| new_values | json | nullable |
| ip_address | varchar(45) | |
| user_agent | text | |
| timestamps | | |

---

## MySQL Views (Mandatory)

### student_results_view
```sql
CREATE VIEW student_results_view AS
SELECT
    s.id              AS student_id,
    s.student_id      AS student_code,
    s.roll_number,
    u.name            AS student_name,
    d.name            AS department,
    c.title           AS course,
    c.code            AS course_code,
    e.title           AS exam_title,
    r.obtained_marks,
    r.total_marks,
    r.percentage,
    r.grade,
    r.gpa,
    r.is_pass,
    r.class_rank,
    r.department_rank,
    r.published_at
FROM results r
JOIN exam_attempts ea ON ea.id = r.attempt_id
JOIN students s       ON s.id  = r.student_id
JOIN users u          ON u.id  = s.user_id
JOIN departments d    ON d.id  = s.department_id
JOIN exams e          ON e.id  = r.exam_id
JOIN courses c        ON c.id  = e.course_id;
```

### course_performance_view
```sql
CREATE VIEW course_performance_view AS
SELECT
    c.id                                              AS course_id,
    c.code,
    c.title,
    d.name                                            AS department,
    COUNT(r.id)                                       AS total_attempts,
    AVG(r.percentage)                                 AS avg_percentage,
    MAX(r.obtained_marks)                             AS highest_marks,
    MIN(r.obtained_marks)                             AS lowest_marks,
    SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)   AS pass_count,
    SUM(CASE WHEN r.is_pass = 0 THEN 1 ELSE 0 END)   AS fail_count,
    ROUND(
        SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)
        / COUNT(r.id) * 100, 2
    )                                                 AS pass_percentage
FROM courses c
JOIN departments d ON d.id = c.department_id
JOIN exams e       ON e.course_id = c.id
JOIN results r     ON r.exam_id = e.id
GROUP BY c.id, c.code, c.title, d.name;
```

### department_performance_view
```sql
CREATE VIEW department_performance_view AS
SELECT
    d.id                                              AS department_id,
    d.name                                            AS department,
    f.name                                            AS faculty,
    COUNT(DISTINCT s.id)                              AS total_students,
    COUNT(r.id)                                       AS total_exams_taken,
    AVG(r.percentage)                                 AS avg_percentage,
    SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)   AS total_passed,
    SUM(CASE WHEN r.is_pass = 0 THEN 1 ELSE 0 END)   AS total_failed,
    ROUND(
        SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)
        / COUNT(r.id) * 100, 2
    )                                                 AS pass_rate
FROM departments d
JOIN faculties f    ON f.id = d.faculty_id
JOIN students s     ON s.department_id = d.id
JOIN results r      ON r.student_id = s.id
GROUP BY d.id, d.name, f.name;
```

---

## SQL Ranking Queries (Mandatory)

### Class Rank (per exam)
```sql
SELECT
    s.student_id,
    u.name,
    r.obtained_marks,
    r.percentage,
    ROW_NUMBER()  OVER (PARTITION BY r.exam_id ORDER BY r.obtained_marks DESC) AS row_num,
    RANK()        OVER (PARTITION BY r.exam_id ORDER BY r.obtained_marks DESC) AS class_rank,
    DENSE_RANK()  OVER (PARTITION BY r.exam_id ORDER BY r.obtained_marks DESC) AS dense_rank
FROM results r
JOIN students s ON s.id = r.student_id
JOIN users u    ON u.id = s.user_id
WHERE r.exam_id = :exam_id AND r.published_at IS NOT NULL;
```

### Department Rank
```sql
SELECT
    s.student_id,
    u.name,
    d.name AS department,
    AVG(r.percentage)   AS avg_percentage,
    RANK() OVER (
        PARTITION BY s.department_id
        ORDER BY AVG(r.percentage) DESC
    ) AS department_rank
FROM results r
JOIN students s    ON s.id = r.student_id
JOIN users u       ON u.id = s.user_id
JOIN departments d ON d.id = s.department_id
GROUP BY s.id, s.student_id, u.name, d.name, s.department_id;
```

### Semester Rank
```sql
SELECT
    s.student_id,
    u.name,
    s.semester,
    AVG(r.percentage)   AS avg_percentage,
    RANK() OVER (
        PARTITION BY s.semester
        ORDER BY AVG(r.percentage) DESC
    ) AS semester_rank
FROM results r
JOIN students s ON s.id = r.student_id
JOIN users u    ON u.id = s.user_id
GROUP BY s.id, s.student_id, u.name, s.semester;
```

### Top N Students Per Course
```sql
SELECT * FROM (
    SELECT
        s.student_id,
        u.name,
        c.title AS course,
        r.percentage,
        DENSE_RANK() OVER (
            PARTITION BY e.course_id
            ORDER BY r.percentage DESC
        ) AS course_rank
    FROM results r
    JOIN exam_attempts ea ON ea.id = r.attempt_id
    JOIN students s       ON s.id  = r.student_id
    JOIN users u          ON u.id  = s.user_id
    JOIN exams e          ON e.id  = r.exam_id
    JOIN courses c        ON c.id  = e.course_id
    WHERE r.published_at IS NOT NULL
) ranked
WHERE course_rank <= 10;
```

---

## OLAP Queries (Mandatory)

### Monthly Exam Performance Trend
```sql
SELECT
    DATE_FORMAT(r.published_at, '%Y-%m')     AS month,
    COUNT(r.id)                               AS total_results,
    AVG(r.percentage)                         AS avg_score,
    MAX(r.percentage)                         AS highest,
    MIN(r.percentage)                         AS lowest,
    SUM(CASE WHEN r.is_pass=1 THEN 1 ELSE 0 END) AS passed,
    SUM(CASE WHEN r.is_pass=0 THEN 1 ELSE 0 END) AS failed
FROM results r
WHERE r.published_at IS NOT NULL
GROUP BY DATE_FORMAT(r.published_at, '%Y-%m')
ORDER BY month;
```

### Semester Performance Matrix (OLAP Pivot)
```sql
SELECT
    d.name                                           AS department,
    s.semester,
    COUNT(DISTINCT s.id)                             AS students,
    AVG(r.percentage)                                AS avg_score,
    SUM(CASE WHEN r.is_pass=1 THEN 1 ELSE 0 END)    AS pass_count,
    RANK() OVER (
        PARTITION BY s.semester
        ORDER BY AVG(r.percentage) DESC
    )                                                AS semester_dept_rank
FROM results r
JOIN students s    ON s.id = r.student_id
JOIN departments d ON d.id = s.department_id
GROUP BY d.name, s.semester
ORDER BY s.semester, semester_dept_rank;
```

### Grade Distribution (for charts)
```sql
SELECT
    grade,
    COUNT(*) AS count,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) AS percentage
FROM results
WHERE published_at IS NOT NULL
GROUP BY grade
ORDER BY FIELD(grade, 'A+', 'A', 'B+', 'B', 'C', 'D', 'F');
```

---

## User Roles & Permissions

Managed by **Spatie Laravel Permission**.

| Permission | super_admin | exam_controller | teacher | student |
|---|---|---|---|---|
| manage users | ✓ | | | |
| manage faculties | ✓ | | | |
| manage departments | ✓ | | | |
| manage courses | ✓ | | | |
| assign teachers | ✓ | | | |
| manage question banks | ✓ | | ✓ | |
| create exams | ✓ | | ✓ | |
| schedule exams | ✓ | ✓ | | |
| monitor live exams | ✓ | ✓ | | |
| publish results | ✓ | ✓ | | |
| view own results | ✓ | | ✓ | ✓ |
| attempt exams | | | | ✓ |
| import students | ✓ | | | |
| view analytics | ✓ | ✓ | ✓ | |
| view audit logs | ✓ | | | |
| generate reports | ✓ | ✓ | ✓ | |
| download result card | | | | ✓ |

---

## Module Details

### Module 1 — Authentication

**Implementation:** Laravel Breeze (Inertia/Vue stack)

Features:
- Registration with email verification
- Login with rate limiting (5 attempts / minute)
- Forgot password + reset via signed URLs
- Remember Me (90-day cookie)
- Two-Factor Authentication (TOTP via `pragmarx/google2fa`)
- Social Login via Google (Laravel Socialite)
- Session management — list & revoke active sessions
- Secure cookies (`http_only`, `same_site=lax`, `secure` in production)
- CSRF on all state-changing routes
- XSS prevention via Blade escaping + CSP headers

Pages: `Login.vue`, `Register.vue`, `ForgotPassword.vue`, `ResetPassword.vue`, `VerifyEmail.vue`, `TwoFactorChallenge.vue`

---

### Module 2 — Faculty Management

CRUD for university faculties.

- Search + pagination (Inertia `preserveState`)
- Soft deletes
- Status toggle (active/inactive)
- Linked to departments count shown in table

Route prefix: `/admin/faculties`
Controller: `Admin\FacultyController`
Service: `FacultyService`
Policy: `FacultyPolicy`

---

### Module 3 — Department Management

CRUD for departments linked to faculties.

- Faculty selector (searchable dropdown)
- Department code uniqueness validation
- Cascade view: faculty → departments → courses

Route prefix: `/admin/departments`
Controller: `Admin\DepartmentController`

---

### Module 4 — Student Management

Full student lifecycle management.

Features:
- CRUD with profile photos
- **Excel Import** — bulk upload via `maatwebsite/excel`, queued job `ImportStudentsJob`
- **Excel Export** — filtered export with column mapping
- Search by name, student ID, roll number, department, semester
- Status management (active / inactive / graduated)
- Student profile page showing: personal info, enrolled courses, exam history, grades

Excel Import format (column order):
`name | email | student_id | roll_number | department_code | semester | batch | phone | address`

Route prefix: `/admin/students` and `/student/profile`
Controller: `Admin\StudentController`, `Student\ProfileController`
Service: `StudentService`
Job: `ImportStudentsJob` (queued, handles validation & error reporting)

---

### Module 5 — Course Management

- CRUD with department association
- Assign multiple teachers to a course
- Enroll students (manually or by department/semester batch)
- Course status management
- Credit hours and semester metadata

---

### Module 6 — Question Bank

Rich question management with multiple types.

#### Question Types
| Type | Key | Auto-graded |
|---|---|---|
| MCQ Single Correct | `mcq_single` | Yes |
| MCQ Multiple Correct | `mcq_multiple` | Yes (all correct options required) |
| True / False | `true_false` | Yes |
| Fill in the Blank | `fill_blank` | Yes (case-insensitive match or exact) |
| Short Question | `short` | No (teacher review) |
| Descriptive / Essay | `descriptive` | No (teacher review) |

Features:
- Rich text editor (TipTap) for question and option text
- Image upload attached to questions (stored in `storage/app/public/questions/`)
- Tagging system (json array) for filtering
- Difficulty: easy / medium / hard
- Question preview modal
- Clone question functionality
- Bank-level statistics (count by type, difficulty breakdown)

---

### Module 7 — Exam Management

Full exam lifecycle:

1. **Draft** — created but not visible to students
2. **Scheduled** — visible, not yet startable
3. **Active** — students can attempt
4. **Completed** — window passed, auto-submitted
5. **Cancelled** — removed from student view

Features:
- Add questions from question bank (with search/filter by type, difficulty, tags)
- Override per-question marks within exam
- Shuffle questions/options toggle
- Allow/disallow backtracking
- Immediate result toggle
- Instructions editor
- Exam preview (teacher view)
- Schedule validation (end > start, duration fits window)

---

### Module 8 — Exam Attempt System

The core student-facing exam experience.

#### Timer
- Countdown from `duration_minutes` using `ExamTimer.vue`
- Stored server-side in `exam_attempts.started_at`
- Auto-submit fires `AutoSubmitExamJob` when time expires
- Visual warning at 5 minutes remaining (yellow), 1 minute (red pulse)

#### Navigation
- Question grid in `QuestionNavigator.vue` showing:
  - Gray = unanswered
  - Green = answered
  - Yellow = marked for review
  - Blue = current
- Previous / Next buttons
- Direct jump by clicking question number

#### Auto-Save
- Every 10 seconds via `setInterval` → `POST /api/exam/save-answer`
- Debounced on input change (immediate save on answer selection)
- Offline detection — queues saves and flushes on reconnect

#### Anti-Cheating
| Event | Detection | Action |
|---|---|---|
| Tab switch | `visibilitychange` event | Increment `tab_switch_count`, log warning |
| Window minimize | `blur` event | Log event |
| Copy/Paste | `copy`, `paste`, `cut` events | `preventDefault()` + log |
| Right click | `contextmenu` event | `preventDefault()` |
| DevTools open | window size heuristic | Flag + log |
| Keyboard shortcuts | F12, Ctrl+U, Ctrl+S, Ctrl+Shift+I | Block all |

All events sent to `POST /api/exam/log-activity` and stored in audit_logs.
After 5 tab switches: show warning modal.
After 10: auto-submit with `status = auto_submitted`.

#### Full Screen
- `document.documentElement.requestFullscreen()` on exam start
- Exit fullscreen = treated as suspicious activity

---

### Module 9 — Result Management

#### Auto Grading (`GradingService`)
Handles: `mcq_single`, `mcq_multiple`, `true_false`, `fill_blank`

For MCQ Multiple: full marks only if ALL correct options selected, zero otherwise (configurable to partial credit).

#### Manual Evaluation
Teacher reviews `short` and `descriptive` answers.
Route: `GET /teacher/exams/{exam}/evaluate`
Shows each student's answer with marks input and feedback textarea.

#### Grade Scale (configurable in `config/examination.php`)
| Percentage | Grade | GPA |
|---|---|---|
| 90–100 | A+ | 4.0 |
| 85–89 | A | 4.0 |
| 80–84 | B+ | 3.5 |
| 75–79 | B | 3.0 |
| 70–74 | C+ | 2.5 |
| 65–69 | C | 2.0 |
| 60–64 | D | 1.0 |
| < 60 | F | 0.0 |

#### Result Card (PDF)
Generated via DomPDF.
Template: `resources/views/pdf/result-card.blade.php`
Contents: student photo, info, course, exam, marks breakdown, grade, GPA, class rank, QR code (verifiable link).

#### Exports
- `GET /results/{result}/pdf` — single result card
- `GET /exams/{exam}/results/excel` — bulk result sheet
- `GET /exams/{exam}/results/csv` — CSV export

---

### Module 10 — Ranking System

`RankingService` computes and caches rankings.

Ranks are recalculated when a result is published (`ResultPublished` event → `ComputeRankingsListener`).

Stored back into `results.class_rank` and `results.department_rank`.

Rankings displayed on:
- Student result card
- Teacher exam results table
- Admin analytics

---

### Module 11 — SQL Views

See the **MySQL Views** section above for complete SQL.

Views are created in a dedicated migration:
`database/migrations/xxxx_create_examination_views.php`

Used by:
- `StudentResultResource` (reads from `student_results_view`)
- `OlapService` (reads from `course_performance_view`, `department_performance_view`)

---

### Module 12 — OLAP Analysis Dashboard

Route: `/admin/analytics/olap`
Component: `Pages/Analytics/OlapDashboard.vue`
Service: `OlapService`

#### Panels
1. **Pass/Fail Distribution** — Pie chart (Chart.js Doughnut)
2. **Average Score by Course** — Horizontal Bar chart
3. **Monthly Performance Trend** — Line/Area chart
4. **Grade Distribution** — Vertical Bar chart
5. **Department vs Semester Heatmap** — custom table with color cells
6. **Top 10 Students** — ranked table

All charts use `vue-chartjs` wrappers.
Data fetched via Inertia shared data or dedicated `/api/analytics/*` endpoints for lazy loading.

---

### Module 13 — Analytics Dashboard

#### Super Admin Dashboard (`Pages/Dashboard/AdminDashboard.vue`)
Stats cards:
- Total Students (with % growth vs last month)
- Total Exams (active, scheduled, completed)
- Total Courses
- Total Departments
- Pass Rate (system-wide)
- Pending Evaluations

Charts:
- Student Growth (monthly line chart)
- Exams Per Month (bar chart)
- Department Performance (radar chart)

Recent Activities feed (from audit_logs, last 20 entries).

#### Teacher Dashboard
- My Courses with student counts
- Pending evaluations count
- Recent exam results
- Question bank stats

#### Student Dashboard
- Upcoming exams
- Recent results
- GPA trend chart
- Department rank badge

---

### Module 14 — Reporting System

`ReportService` powers all reports.

| Report | Route | Format |
|---|---|---|
| Student Result Report | `/reports/student/{student}` | PDF, Excel |
| Course Result Report | `/reports/course/{course}` | PDF, Excel, CSV |
| Department Report | `/reports/department/{department}` | PDF, Excel |
| Performance Report | `/reports/performance` | PDF, Excel |
| Exam Summary | `/reports/exam/{exam}` | PDF, Excel |

All PDF templates in `resources/views/pdf/`.
All Excel exports extend `Maatwebsite\Excel\Concerns\FromQuery`.

---

### Module 15 — Notification System

`App\Notifications\` classes, all implement both `mail` and `database` channels.

| Notification | Trigger | Channels |
|---|---|---|
| `ExamScheduledNotification` | Exam status → scheduled | mail, database |
| `ExamReminderNotification` | 24h before start (scheduled job) | mail, database |
| `ResultPublishedNotification` | Result published | mail, database |
| `EvaluationRequiredNotification` | Exam completed, descriptive answers pending | mail, database |

In-app notification bell in `Navbar.vue` reads from `notifications` table.
Notification preferences per user stored in user settings.

---

### Module 16 — Audit Log System

`AuditService::log(event, auditable, data)` called from:
- `AuthService` (login, logout, failed login)
- `ExamAttemptService` (start, auto-save, suspicious activity, submit)
- `ResultService` (publish, manual edit)
- `Admin\UserController` (user created, role changed)

Stored in `audit_logs` table with full IP, user agent, old/new values.
Admin view: `Pages/Admin/AuditLogs.vue` — searchable, filterable by event type, user, date range.

---

## Routes Overview

```
GET  /                          → redirect based on role
GET  /login                     → Auth/Login.vue
GET  /register                  → Auth/Register.vue

# Super Admin (middleware: auth, role:super_admin)
GET  /admin/dashboard
GET  /admin/users
GET  /admin/faculties
GET  /admin/departments
GET  /admin/students
GET  /admin/courses
GET  /admin/exams
GET  /admin/analytics
GET  /admin/analytics/olap
GET  /admin/reports
GET  /admin/audit-logs

# Teacher (middleware: auth, role:teacher)
GET  /teacher/dashboard
GET  /teacher/question-banks
GET  /teacher/exams
GET  /teacher/exams/{exam}/evaluate
GET  /teacher/results
GET  /teacher/analytics

# Exam Controller (middleware: auth, role:exam_controller)
GET  /controller/dashboard
GET  /controller/exams
GET  /controller/exams/{exam}/monitor
GET  /controller/results

# Student (middleware: auth, role:student, verified)
GET  /student/dashboard
GET  /student/exams
GET  /student/exams/{exam}/attempt    → live exam UI
GET  /student/results
GET  /student/results/{result}/card
GET  /student/profile

# Internal API (middleware: auth)
POST /api/exam/{attempt}/save-answer
POST /api/exam/{attempt}/log-activity
POST /api/exam/{attempt}/submit
GET  /api/analytics/olap/{type}
```

---

## Key Configuration Files

### `config/examination.php`
```php
return [
    'grade_scale' => [
        ['min' => 90, 'grade' => 'A+', 'gpa' => 4.0],
        ['min' => 85, 'grade' => 'A',  'gpa' => 4.0],
        // ...
    ],
    'auto_save_interval_seconds' => 10,
    'tab_switch_warning_threshold' => 5,
    'tab_switch_submit_threshold'  => 10,
    'mcq_partial_credit' => false,
    'result_card_qr' => true,
];
```

### `.env` (key values to set)
```
APP_NAME="University Exam System"
APP_ENV=local
DB_CONNECTION=mysql
DB_DATABASE=online_examination_system
MAIL_MAILER=smtp
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

---

## Development Commands

```bash
# Initial setup
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev

# Run all services together
composer dev

# Run tests
composer test

# Queue worker
php artisan queue:listen --tries=3

# Generate IDE helpers
php artisan ide-helper:generate
php artisan ide-helper:models -W

# Clear all caches
php artisan optimize:clear

# Lint (Laravel Pint)
./vendor/bin/pint
```

---

## Testing Strategy

### Feature Tests (`tests/Feature/`)
- `AuthTest` — registration, login, 2FA, rate limiting, social login
- `StudentManagementTest` — CRUD, Excel import/export
- `ExamManagementTest` — create, schedule, status transitions
- `ExamAttemptTest` — start, answer, auto-save, submit, anti-cheat logging
- `GradingTest` — auto-grade MCQ/TF/fill, manual grade submission
- `ResultTest` — publish, PDF generation, Excel export
- `RankingTest` — class rank, department rank computation
- `NotificationTest` — exam scheduled, result published dispatched

### Unit Tests (`tests/Unit/`)
- `GradingServiceTest` — grade calculation, GPA mapping
- `RankingServiceTest` — window function query correctness
- `OlapServiceTest` — aggregation queries, chart data shape

### Test Database
Uses in-memory SQLite for unit tests, a separate `online_examination_system_test` MySQL DB for feature tests that require window functions/views.

---

## UI/UX Design System

### Color Palette
```css
/* Primary */
--primary: 220 90% 56%;          /* Indigo */
--primary-foreground: 0 0% 98%;

/* Accent */
--accent: 262 80% 60%;           /* Violet */

/* Success / Danger / Warning */
--success: 142 71% 45%;
--danger:  0 84% 60%;
--warning: 38 92% 50%;
```

### Typography
- Headings: `Inter` (font-weight 600–800)
- Body: `Inter` (font-weight 400–500)
- Mono: `JetBrains Mono` (code blocks, student IDs)

### Component Patterns
- Cards with glassmorphism: `backdrop-blur-sm bg-white/80 dark:bg-slate-900/80`
- Tables: sticky header, zebra rows, sort indicators
- Forms: floating labels, inline validation, loading states on submit
- Stats cards: icon + number + percentage delta badge
- Charts: rounded bars, smooth line curves, tooltip on hover

### Dark Mode
Implemented via Tailwind `dark:` classes + `class` strategy.
Theme toggle persisted in `localStorage` and synced across tabs.

---

## Docker Setup

`docker-compose.yml` services:
- `app` — PHP 8.3 FPM (Dockerfile with extensions: pdo_mysql, gd, zip, bcmath, opcache)
- `nginx` — Nginx reverse proxy
- `db` — MySQL 8
- `redis` — Redis (for queue/cache in production)
- `mailpit` — local email catcher (dev)
- `queue` — runs `php artisan queue:listen`

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

---

## Security Checklist

- [x] RBAC via Spatie — every route behind `role:` middleware
- [x] Policies — every model has a Policy class registered in `AuthServiceProvider`
- [x] SQL Injection — Eloquent ORM + parameterized queries only, zero raw string interpolation
- [x] XSS — Blade auto-escaping, Vue template auto-escaping, TipTap output sanitized server-side
- [x] CSRF — Laravel CSRF middleware on all non-GET routes; `X-CSRF-TOKEN` header in Axios
- [x] Rate Limiting — `throttle:5,1` on login; `throttle:60,1` on API routes
- [x] Password Hashing — `bcrypt` with 12 rounds (env `BCRYPT_ROUNDS=12`)
- [x] File Uploads — MIME validation, extension whitelist, stored outside public root
- [x] Secure Headers — `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`
- [x] Exam Integrity — server-side time validation, answers validated against known questions/options

---

## Build Phases

| Phase | Status | Description |
|---|---|---|
| 1 | Planned | SRS Document |
| 2 | Planned | Database Design + ERD |
| 3 | Planned | Migrations (all tables + views) |
| 4 | Planned | Models & Relationships |
| 5 | Planned | Policies, Services, Repositories |
| 6 | Planned | Controllers + Form Requests |
| 7 | Planned | Authentication (Breeze + 2FA + Google) |
| 8 | Planned | Faculty / Department / Course modules |
| 9 | Planned | Student Management (CRUD + Excel) |
| 10 | Planned | Question Bank (all types + rich editor) |
| 11 | Planned | Exam Management (lifecycle) |
| 12 | Planned | Exam Attempt System (timer + anti-cheat) |
| 13 | Planned | Auto-grading + Manual Evaluation |
| 14 | Planned | Result Management + PDF/Excel export |
| 15 | Planned | Ranking Queries + Views |
| 16 | Planned | OLAP Dashboard + Analytics |
| 17 | Planned | Notifications + Audit Logs |
| 18 | Planned | Reporting System |
| 19 | Planned | Testing Suite |
| 20 | Planned | Docker + Deployment |

---

## Notes for Claude

- This is a Laravel 13 project (framework `^13.8`), not Laravel 12. Adjust any docs/API calls accordingly.
- The `.env` currently has `DB_CONNECTION=sqlmysqlite` — this must be corrected to `mysql` before running migrations.
- No Inertia, Vue, or Spatie packages are installed yet. Install them before generating pages/components.
- The project is a **university Final Year Project** targeting a distinction grade — prioritize clean code, full coverage of mandatory requirements (ranking queries, SQL views, OLAP), and a polished UI.
- Always use **Service layer** for business logic — controllers must only handle HTTP concerns (validate, call service, return Inertia response).
- **Mandatory SQL features** (Views, Window Functions, OLAP aggregations) must be real SQL — not faked in PHP.
- Student-facing exam attempt page is the most critical UX — it must be rock-solid (auto-save, timer, offline resilience).
- All exports (PDF, Excel) must be queued for exams with many students — never block the HTTP request.
- When generating migrations, use `Schema::create` with proper foreign key constraints and indexes on all FK columns and frequently-queried columns (`status`, `exam_id`, `student_id`, `published_at`).
