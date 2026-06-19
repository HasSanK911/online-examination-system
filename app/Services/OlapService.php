<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OlapService
{
    public function getMonthlyTrend(): array
    {
        $rows = DB::select("
            SELECT
                DATE_FORMAT(r.published_at, '%Y-%m')                             AS month,
                DATE_FORMAT(r.published_at, '%b %Y')                             AS label,
                COUNT(r.id)                                                       AS total,
                ROUND(AVG(r.percentage), 2)                                      AS avg_score,
                MAX(r.percentage)                                                 AS highest,
                MIN(r.percentage)                                                 AS lowest,
                SUM(CASE WHEN r.is_pass = 1 THEN 1 ELSE 0 END)                   AS passed,
                SUM(CASE WHEN r.is_pass = 0 THEN 1 ELSE 0 END)                   AS failed
            FROM results r
            WHERE r.published_at IS NOT NULL
            GROUP BY DATE_FORMAT(r.published_at, '%Y-%m'), DATE_FORMAT(r.published_at, '%b %Y')
            ORDER BY month
            LIMIT 12
        ");
        return array_map(fn($r) => (array) $r, $rows);
    }

    public function getGradeDistribution(): array
    {
        $rows = DB::select("
            SELECT
                grade,
                COUNT(*) AS count,
                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) AS percentage
            FROM results
            WHERE published_at IS NOT NULL AND grade IS NOT NULL
            GROUP BY grade
            ORDER BY FIELD(grade, 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F')
        ");
        return array_map(fn($r) => (array) $r, $rows);
    }

    public function getDepartmentPerformance(): array
    {
        $rows = DB::select("
            SELECT
                department,
                department_code,
                faculty,
                total_students,
                total_results,
                avg_percentage,
                total_passed,
                total_failed,
                pass_rate
            FROM department_performance_view
            ORDER BY avg_percentage DESC
        ");
        return array_map(fn($r) => (array) $r, $rows);
    }

    public function getCoursePerformance(): array
    {
        $rows = DB::select("
            SELECT
                course_id,
                code,
                title,
                department,
                total_results,
                avg_percentage,
                highest_marks,
                lowest_marks,
                pass_count,
                fail_count,
                pass_percentage
            FROM course_performance_view
            ORDER BY avg_percentage DESC
        ");
        return array_map(fn($r) => (array) $r, $rows);
    }

    public function getSemesterMatrix(): array
    {
        $rows = DB::select("
            SELECT
                d.name                                              AS department,
                s.semester,
                COUNT(DISTINCT s.id)                               AS students,
                ROUND(AVG(r.percentage), 2)                        AS avg_score,
                SUM(CASE WHEN r.is_pass=1 THEN 1 ELSE 0 END)       AS pass_count,
                RANK() OVER (
                    PARTITION BY s.semester
                    ORDER BY AVG(r.percentage) DESC
                ) AS semester_dept_rank
            FROM results r
            JOIN students s    ON s.id = r.student_id
            JOIN departments d ON d.id = s.department_id
            WHERE r.published_at IS NOT NULL
            GROUP BY d.name, s.semester
            ORDER BY s.semester, semester_dept_rank
        ");
        return array_map(fn($r) => (array) $r, $rows);
    }

    public function getOverviewStats(): array
    {
        $stats = DB::selectOne("
            SELECT
                COUNT(DISTINCT student_id)                                          AS total_students_examined,
                COUNT(*)                                                             AS total_results,
                ROUND(AVG(percentage), 2)                                            AS overall_avg,
                ROUND(SUM(CASE WHEN is_pass=1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS pass_rate,
                MAX(percentage)                                                      AS highest,
                MIN(percentage)                                                      AS lowest
            FROM results
            WHERE published_at IS NOT NULL
        ");
        return (array) $stats;
    }
}
