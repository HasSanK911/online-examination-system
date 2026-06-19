<?php

namespace Tests\Unit;

use App\Services\GradingService;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    private GradingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GradingService::class);
    }

    public function test_grade_scale_boundaries(): void
    {
        $cases = [
            [100, 'A+', 4.0],
            [90,  'A+', 4.0],
            [89,  'A',  4.0],
            [85,  'A',  4.0],
            [84,  'B+', 3.5],
            [80,  'B+', 3.5],
            [79,  'B',  3.0],
            [75,  'B',  3.0],
            [74,  'C+', 2.5],
            [70,  'C+', 2.5],
            [69,  'C',  2.0],
            [65,  'C',  2.0],
            [64,  'D',  1.0],
            [60,  'D',  1.0],
            [59,  'F',  0.0],
            [0,   'F',  0.0],
        ];

        foreach ($cases as [$pct, $expectedGrade, $expectedGpa]) {
            $result = $this->service->calculateGrade($pct);
            $this->assertEquals($expectedGrade, $result['grade'], "Grade mismatch at {$pct}%");
            $this->assertEquals($expectedGpa,   $result['gpa'],   "GPA mismatch at {$pct}%");
        }
    }
}
