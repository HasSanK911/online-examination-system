<?php

return [
    'grade_scale' => [
        ['min' => 90, 'grade' => 'A+', 'gpa' => 4.00],
        ['min' => 85, 'grade' => 'A',  'gpa' => 4.00],
        ['min' => 80, 'grade' => 'B+', 'gpa' => 3.50],
        ['min' => 75, 'grade' => 'B',  'gpa' => 3.00],
        ['min' => 70, 'grade' => 'C+', 'gpa' => 2.50],
        ['min' => 65, 'grade' => 'C',  'gpa' => 2.00],
        ['min' => 60, 'grade' => 'D',  'gpa' => 1.00],
        ['min' => 0,  'grade' => 'F',  'gpa' => 0.00],
    ],

    'passing_percentage' => 50,
    'auto_save_interval_seconds' => 10,
    'tab_switch_warning_threshold' => 5,
    'tab_switch_submit_threshold' => 10,
    'mcq_partial_credit' => false,
    'result_card_qr' => true,

    'student_id_prefix_format' => '{DEPT}-{YEAR}-{SEQ}',
    'max_exam_duration_minutes' => 300,
    'default_exam_duration_minutes' => 60,
];
