<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS student_results_view');
        DB::statement("
            CREATE VIEW student_results_view AS
            SELECT
                s.id              AS student_id,
                s.student_id      AS student_code,
                s.roll_number,
                s.semester,
                s.batch,
                u.name            AS student_name,
                u.email           AS student_email,
                d.name            AS department,
                d.code            AS department_code,
                f.name            AS faculty,
                c.title           AS course,
                c.code            AS course_code,
                c.credit_hours,
                e.id              AS exam_id,
                e.title           AS exam_title,
                e.total_marks,
                r.id              AS result_id,
                r.obtained_marks,
                r.percentage,
                r.grade,
                r.gpa,
                r.is_pass,
                r.class_rank,
                r.department_rank,
                r.semester_rank,
                r.published_at
            FROM results r
            JOIN exam_attempts ea ON ea.id  = r.attempt_id
            JOIN students s       ON s.id   = r.student_id
            JOIN users u          ON u.id   = s.user_id
            JOIN departments d    ON d.id   = s.department_id
            JOIN faculties f      ON f.id   = d.faculty_id
            JOIN exams e          ON e.id   = r.exam_id
            JOIN courses c        ON c.id   = e.course_id
            WHERE r.published_at IS NOT NULL
        ");

        DB::statement('DROP VIEW IF EXISTS course_performance_view');
        DB::statement("
            CREATE VIEW course_performance_view AS
            SELECT
                c.id                                                                    AS course_id,
                c.code,
                c.title,
                c.credit_hours,
                d.id                                                                    AS department_id,
                d.name                                                                  AS department,
                f.name                                                                  AS faculty,
                COUNT(r.id)                                                             AS total_results,
                ROUND(AVG(r.percentage), 2)                                             AS avg_percentage,
                MAX(r.obtained_marks)                                                   AS highest_marks,
                MIN(r.obtained_marks)                                                   AS lowest_marks,
                SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)                         AS pass_count,
                SUM(CASE WHEN r.is_pass = 0 THEN 1 ELSE 0 END)                         AS fail_count,
                ROUND(SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END) / COUNT(r.id) * 100, 2) AS pass_percentage
            FROM courses c
            JOIN departments d  ON d.id = c.department_id
            JOIN faculties f    ON f.id = d.faculty_id
            JOIN exams e        ON e.course_id = c.id
            JOIN results r      ON r.exam_id = e.id
            WHERE r.published_at IS NOT NULL
            GROUP BY c.id, c.code, c.title, c.credit_hours, d.id, d.name, f.name
        ");

        DB::statement('DROP VIEW IF EXISTS department_performance_view');
        DB::statement("
            CREATE VIEW department_performance_view AS
            SELECT
                d.id                                                                        AS department_id,
                d.name                                                                      AS department,
                d.code                                                                      AS department_code,
                f.id                                                                        AS faculty_id,
                f.name                                                                      AS faculty,
                COUNT(DISTINCT s.id)                                                        AS total_students,
                COUNT(DISTINCT e.id)                                                        AS total_exams,
                COUNT(r.id)                                                                 AS total_results,
                ROUND(AVG(r.percentage), 2)                                                 AS avg_percentage,
                SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)                             AS total_passed,
                SUM(CASE WHEN r.is_pass = 0 THEN 1 ELSE 0 END)                             AS total_failed,
                ROUND(SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END) / COUNT(r.id) * 100, 2) AS pass_rate
            FROM departments d
            JOIN faculties f    ON f.id = d.faculty_id
            JOIN students s     ON s.department_id = d.id
            JOIN results r      ON r.student_id = s.id
            JOIN exams e        ON e.id = r.exam_id
            WHERE r.published_at IS NOT NULL
            GROUP BY d.id, d.name, d.code, f.id, f.name
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS department_performance_view');
        DB::statement('DROP VIEW IF EXISTS course_performance_view');
        DB::statement('DROP VIEW IF EXISTS student_results_view');
    }
};
