<?php

namespace App\Services;

use App\Models\Result;
use Illuminate\Support\Facades\DB;

class RankingService
{
    public function computeExamRankings(int $examId): void
    {
        // Uses MySQL window functions — RANK() and DENSE_RANK()
        DB::statement("
            UPDATE results r
            JOIN (
                SELECT
                    id,
                    RANK() OVER (
                        PARTITION BY exam_id
                        ORDER BY obtained_marks DESC
                    ) AS computed_rank
                FROM results
                WHERE exam_id = ?
                  AND published_at IS NOT NULL
            ) ranked ON ranked.id = r.id
            SET r.class_rank = ranked.computed_rank
            WHERE r.exam_id = ?
        ", [$examId, $examId]);
    }

    public function computeDepartmentRankings(int $examId): void
    {
        DB::statement("
            UPDATE results r
            JOIN students s ON s.id = r.student_id
            JOIN (
                SELECT
                    r2.id,
                    RANK() OVER (
                        PARTITION BY s2.department_id
                        ORDER BY r2.obtained_marks DESC
                    ) AS dept_rank
                FROM results r2
                JOIN students s2 ON s2.id = r2.student_id
                WHERE r2.exam_id = ?
                  AND r2.published_at IS NOT NULL
            ) ranked ON ranked.id = r.id
            SET r.department_rank = ranked.dept_rank
            WHERE r.exam_id = ?
        ", [$examId, $examId]);
    }

    public function computeSemesterRankings(int $examId): void
    {
        DB::statement("
            UPDATE results r
            JOIN students s ON s.id = r.student_id
            JOIN (
                SELECT
                    r2.id,
                    RANK() OVER (
                        PARTITION BY s2.semester
                        ORDER BY r2.obtained_marks DESC
                    ) AS sem_rank
                FROM results r2
                JOIN students s2 ON s2.id = r2.student_id
                WHERE r2.exam_id = ?
                  AND r2.published_at IS NOT NULL
            ) ranked ON ranked.id = r.id
            SET r.semester_rank = ranked.sem_rank
            WHERE r.exam_id = ?
        ", [$examId, $examId]);
    }

    public function getClassRankings(int $examId): \Illuminate\Support\Collection
    {
        return collect(DB::select("
            SELECT
                s.student_id                          AS student_code,
                u.name                                AS student_name,
                d.name                                AS department,
                r.obtained_marks,
                r.total_marks,
                r.percentage,
                r.grade,
                ROW_NUMBER() OVER (ORDER BY r.obtained_marks DESC) AS row_num,
                RANK()       OVER (ORDER BY r.obtained_marks DESC) AS class_rank,
                DENSE_RANK() OVER (ORDER BY r.obtained_marks DESC) AS dense_rank
            FROM results r
            JOIN students s    ON s.id  = r.student_id
            JOIN users u       ON u.id  = s.user_id
            JOIN departments d ON d.id  = s.department_id
            WHERE r.exam_id = ?
              AND r.published_at IS NOT NULL
            ORDER BY r.obtained_marks DESC
        ", [$examId]));
    }

    public function getTopStudentsPerCourse(int $limit = 10): \Illuminate\Support\Collection
    {
        return collect(DB::select("
            SELECT * FROM (
                SELECT
                    s.student_id   AS student_code,
                    u.name         AS student_name,
                    c.title        AS course,
                    c.code         AS course_code,
                    r.percentage,
                    r.grade,
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
            WHERE course_rank <= ?
            ORDER BY course_code, course_rank
        ", [$limit]));
    }

    public function getDepartmentRankingTable(): \Illuminate\Support\Collection
    {
        return collect(DB::select("
            SELECT
                s.student_id    AS student_code,
                u.name          AS student_name,
                d.name          AS department,
                AVG(r.percentage) AS avg_percentage,
                RANK() OVER (
                    PARTITION BY s.department_id
                    ORDER BY AVG(r.percentage) DESC
                ) AS department_rank
            FROM results r
            JOIN students s    ON s.id = r.student_id
            JOIN users u       ON u.id = s.user_id
            JOIN departments d ON d.id = s.department_id
            WHERE r.published_at IS NOT NULL
            GROUP BY s.id, s.student_id, u.name, d.name, s.department_id
            ORDER BY d.name, department_rank
        "));
    }
}
