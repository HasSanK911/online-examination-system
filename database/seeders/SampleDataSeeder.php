<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Faculty;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Faculties ─────────────────────────────────────────────────────────
        $fcs = Faculty::firstOrCreate(['code' => 'FCS'], [
            'name' => 'Faculty of Computer Science', 'dean_name' => 'Prof. Dr. Nadia Khan', 'status' => 'active',
        ]);
        $fe = Faculty::firstOrCreate(['code' => 'FE'], [
            'name' => 'Faculty of Engineering', 'dean_name' => 'Prof. Dr. Ali Hassan', 'status' => 'active',
        ]);
        $fb = Faculty::firstOrCreate(['code' => 'FB'], [
            'name' => 'Faculty of Business', 'dean_name' => 'Prof. Dr. Omar Farooq', 'status' => 'active',
        ]);

        // ── Departments ───────────────────────────────────────────────────────
        $cs  = Department::firstOrCreate(['code' => 'CS'],  ['faculty_id' => $fcs->id, 'name' => 'Computer Science',        'head_name' => 'Dr. Usman Malik',  'status' => 'active']);
        $se  = Department::firstOrCreate(['code' => 'SE'],  ['faculty_id' => $fcs->id, 'name' => 'Software Engineering',    'head_name' => 'Dr. Fatima Zahra', 'status' => 'active']);
        $ee  = Department::firstOrCreate(['code' => 'EE'],  ['faculty_id' => $fe->id,  'name' => 'Electrical Engineering',  'head_name' => 'Dr. Bilal Ahmed',  'status' => 'active']);
        $mba = Department::firstOrCreate(['code' => 'MBA'], ['faculty_id' => $fb->id,  'name' => 'Business Administration', 'head_name' => 'Dr. Zara Hussain',  'status' => 'active']);

        // ── Courses ───────────────────────────────────────────────────────────
        $cs301  = Course::firstOrCreate(['code' => 'CS301'],  ['department_id' => $cs->id,  'title' => 'Database Systems',               'credit_hours' => 3, 'semester' => 5, 'status' => 'active']);
        $cs401  = Course::firstOrCreate(['code' => 'CS401'],  ['department_id' => $cs->id,  'title' => 'Operating Systems',              'credit_hours' => 3, 'semester' => 7, 'status' => 'active']);
        $cs201  = Course::firstOrCreate(['code' => 'CS201'],  ['department_id' => $cs->id,  'title' => 'Data Structures & Algorithms',   'credit_hours' => 3, 'semester' => 3, 'status' => 'active']);
        $se201  = Course::firstOrCreate(['code' => 'SE201'],  ['department_id' => $se->id,  'title' => 'Software Engineering Principles','credit_hours' => 3, 'semester' => 3, 'status' => 'active']);
        $se301  = Course::firstOrCreate(['code' => 'SE301'],  ['department_id' => $se->id,  'title' => 'Design Patterns',                'credit_hours' => 3, 'semester' => 5, 'status' => 'active']);
        $ee201  = Course::firstOrCreate(['code' => 'EE201'],  ['department_id' => $ee->id,  'title' => 'Circuit Analysis',               'credit_hours' => 4, 'semester' => 3, 'status' => 'active']);
        $mba101 = Course::firstOrCreate(['code' => 'MBA101'], ['department_id' => $mba->id, 'title' => 'Principles of Management',       'credit_hours' => 3, 'semester' => 1, 'status' => 'active']);

        // ── Teachers ──────────────────────────────────────────────────────────
        $t1 = User::firstOrCreate(['email' => 'teacher@uniexam.local'], [
            'name' => 'Dr. Sarah Ahmed', 'password' => Hash::make('password'),
            'email_verified_at' => now(), 'is_active' => true,
        ]);
        $t1->syncRoles(['teacher']);

        $t2 = User::firstOrCreate(['email' => 'dr.khan@uniexam.local'], [
            'name' => 'Dr. Zubair Khan', 'password' => Hash::make('password'),
            'email_verified_at' => now(), 'is_active' => true,
        ]);
        $t2->syncRoles(['teacher']);

        $cs301->teachers()->syncWithoutDetaching([$t1->id]);
        $cs401->teachers()->syncWithoutDetaching([$t1->id]);
        $cs201->teachers()->syncWithoutDetaching([$t1->id]);
        $se201->teachers()->syncWithoutDetaching([$t2->id]);
        $ee201->teachers()->syncWithoutDetaching([$t2->id]);

        // ── Students (20 total) ───────────────────────────────────────────────
        $defs = [
            ['Ahmed Raza',     'ahmed@student.uniexam.local',   'CS-2021-001','21-CS-001', $cs->id,  5,'2021-2025'],
            ['Sara Khan',      'sara@student.uniexam.local',    'CS-2021-002','21-CS-002', $cs->id,  5,'2021-2025'],
            ['Omar Farhan',    'omar@student.uniexam.local',    'CS-2021-003','21-CS-003', $cs->id,  5,'2021-2025'],
            ['Hina Baig',      'hina@student.uniexam.local',    'CS-2021-004','21-CS-004', $cs->id,  5,'2021-2025'],
            ['Kamran Ali',     'kamran@student.uniexam.local',  'CS-2021-005','21-CS-005', $cs->id,  5,'2021-2025'],
            ['Zainab Malik',   'zainab@student.uniexam.local',  'CS-2021-006','21-CS-006', $cs->id,  5,'2021-2025'],
            ['Usman Siddiqui', 'usman@student.uniexam.local',   'CS-2022-001','22-CS-001', $cs->id,  3,'2022-2026'],
            ['Madiha Tariq',   'madiha@student.uniexam.local',  'CS-2022-002','22-CS-002', $cs->id,  3,'2022-2026'],
            ['Bilal Ashraf',   'bilal@student.uniexam.local',   'SE-2021-001','21-SE-001', $se->id,  5,'2021-2025'],
            ['Fatima Iqbal',   'fatima@student.uniexam.local',  'SE-2021-002','21-SE-002', $se->id,  5,'2021-2025'],
            ['Shahid Mehmood', 'shahid@student.uniexam.local',  'SE-2021-003','21-SE-003', $se->id,  5,'2021-2025'],
            ['Nadia Hussain',  'nadia@student.uniexam.local',   'SE-2021-004','21-SE-004', $se->id,  5,'2021-2025'],
            ['Tariq Jamil',    'tariq@student.uniexam.local',   'SE-2022-001','22-SE-001', $se->id,  3,'2022-2026'],
            ['Ayesha Sohail',  'ayesha@student.uniexam.local',  'SE-2022-002','22-SE-002', $se->id,  3,'2022-2026'],
            ['Hamza Qureshi',  'hamza@student.uniexam.local',   'EE-2022-001','22-EE-001', $ee->id,  3,'2022-2026'],
            ['Sana Rehman',    'sana@student.uniexam.local',    'EE-2022-002','22-EE-002', $ee->id,  3,'2022-2026'],
            ['Wahab Mirza',    'wahab@student.uniexam.local',   'EE-2022-003','22-EE-003', $ee->id,  3,'2022-2026'],
            ['Lubna Arif',     'lubna@student.uniexam.local',   'EE-2022-004','22-EE-004', $ee->id,  3,'2022-2026'],
            ['Junaid Akhtar',  'junaid@student.uniexam.local',  'MBA-2023-001','23-MBA-001',$mba->id, 1,'2023-2025'],
            ['Rabia Nasir',    'rabia@student.uniexam.local',   'MBA-2023-002','23-MBA-002',$mba->id, 1,'2023-2025'],
        ];

        $students = [];
        foreach ($defs as [$name, $email, $sid, $roll, $deptId, $sem, $batch]) {
            $u = User::firstOrCreate(['email' => $email], [
                'name' => $name, 'password' => Hash::make('password'),
                'email_verified_at' => now(), 'is_active' => true,
            ]);
            $u->syncRoles(['student']);
            $students[$sid] = Student::firstOrCreate(['student_id' => $sid], [
                'user_id'        => $u->id,
                'department_id'  => $deptId,
                'roll_number'    => $roll,
                'semester'       => $sem,
                'batch'          => $batch,
                'status'         => 'active',
                'enrollment_date'=> now()->subMonths(rand(3, 24)),
            ]);
        }

        // ── Enrollments ───────────────────────────────────────────────────────
        foreach (['CS-2021-001','CS-2021-002','CS-2021-003','CS-2021-004','CS-2021-005','CS-2021-006'] as $sid) {
            $cs301->students()->syncWithoutDetaching([$students[$sid]->id]);
            $cs401->students()->syncWithoutDetaching([$students[$sid]->id]);
        }
        foreach (['CS-2022-001','CS-2022-002'] as $sid) {
            $cs201->students()->syncWithoutDetaching([$students[$sid]->id]);
        }
        foreach (['SE-2021-001','SE-2021-002','SE-2021-003','SE-2021-004','SE-2022-001','SE-2022-002'] as $sid) {
            $se201->students()->syncWithoutDetaching([$students[$sid]->id]);
        }
        foreach (['EE-2022-001','EE-2022-002','EE-2022-003','EE-2022-004'] as $sid) {
            $ee201->students()->syncWithoutDetaching([$students[$sid]->id]);
        }

        // ── Question Bank 1: Database Systems ────────────────────────────────
        $bank1 = QuestionBank::firstOrCreate(['title' => 'Database Systems Question Bank'], [
            'course_id' => $cs301->id, 'user_id' => $t1->id,
            'description' => 'Comprehensive MCQ bank for CS301', 'status' => 'active',
        ]);

        $dbQs = [
            ['Which SQL clause is used to filter records?','WHERE','HAVING','GROUP BY','ORDER BY', 0,'easy'],
            ['Which normal form eliminates transitive dependencies?','3NF','1NF','2NF','BCNF', 0,'medium'],
            ['A primary key must be:','Unique and not null','Unique only','Not null only','None of the above', 0,'easy'],
            ['ACID stands for:','Atomicity, Consistency, Isolation, Durability','Access, Consistency, Integrity, Data','Atomicity, Control, Integrity, Durability','Access, Control, Isolation, Data', 0,'medium'],
            ['Which join returns all rows from both tables?','FULL OUTER JOIN','INNER JOIN','LEFT JOIN','RIGHT JOIN', 0,'easy'],
            ['An index on a column helps with:','Faster SELECT queries','Faster INSERT queries','Reducing storage','Enforcing uniqueness only', 0,'medium'],
            ['Which SQL command removes a table completely?','DROP TABLE','DELETE TABLE','REMOVE TABLE','TRUNCATE TABLE', 0,'easy'],
            ['A foreign key:','References a primary key in another table','Must be unique','Cannot be null','Is always indexed automatically', 0,'medium'],
            ['What does GROUP BY do?','Groups rows sharing the same values','Sorts the results','Filters groups using conditions','Creates indexes on grouped columns', 0,'easy'],
            ['Normalization is the process of:','Organizing data to reduce redundancy','Increasing query speed','Adding more indexes','Merging two tables', 0,'medium'],
        ];
        $bank1Qs = $this->makeMcqBank($bank1->id, $dbQs);

        // ── Question Bank 2: Software Engineering ─────────────────────────────
        $bank2 = QuestionBank::firstOrCreate(['title' => 'Software Engineering Question Bank'], [
            'course_id' => $se201->id, 'user_id' => $t2->id,
            'description' => 'MCQ bank for SE201', 'status' => 'active',
        ]);

        $seQs = [
            ['What does SDLC stand for?','Software Development Life Cycle','System Design Logic Concept','Software Design and Layout Concept','System Development Life Control', 0,'easy'],
            ['Which model uses feedback loops in sequential phases?','V-Model','Spiral Model','Agile','RAD', 0,'medium'],
            ['A use case diagram belongs to:','UML','ERD','DFD','Flowchart', 0,'easy'],
            ['SRP means a class should:','Have only one reason to change','Be open for extension','Accept any type','Depend on abstractions', 0,'medium'],
            ['Agile focuses on:','Iterative development','Sequential phases','Documentation first','Fixed requirements', 0,'easy'],
            ['A prototype is used to:','Validate requirements early','Write final code','Deploy the product','Generate test cases', 0,'medium'],
            ['System testing verifies:','The entire system as a whole','Individual functions','Integrated modules','Regression scenarios', 0,'easy'],
            ['Scrum iterations are called:','Sprints','Phases','Milestones','Releases', 0,'easy'],
            ['Cohesion refers to:','How closely related module functions are','Dependency between modules','Code reusability','Error handling depth', 0,'hard'],
            ['A non-functional requirement example is:','System response time < 2s','User login feature','Report generation','Data export to Excel', 0,'medium'],
        ];
        $bank2Qs = $this->makeMcqBank($bank2->id, $seQs);

        // ── Exam 1: DB Systems Mid-Term (COMPLETED + published) ───────────────
        $exam1 = Exam::firstOrCreate(['title' => 'Database Systems Mid-Term'], [
            'course_id' => $cs301->id, 'created_by' => $t1->id,
            'description'             => 'Mid-term examination covering Chapters 1–5',
            'total_marks'             => 50,  'passing_marks' => 30,
            'duration_minutes'        => 90,
            'start_time'              => now()->subDays(14),
            'end_time'                => now()->subDays(14)->addHours(2),
            'status'                  => 'completed',
            'shuffle_questions'       => false, 'shuffle_options' => false,
            'allow_backtrack'         => true,  'show_result_immediately' => false,
            'instructions'            => 'Answer all 10 questions. Each carries 5 marks. No negative marking.',
        ]);

        $this->attachQuestionsToExam($exam1, $bank1Qs, 5);

        $scores1 = [47, 43, 40, 38, 35, 27];
        $cs5 = ['CS-2021-001','CS-2021-002','CS-2021-003','CS-2021-004','CS-2021-005','CS-2021-006'];
        foreach ($cs5 as $i => $sid) {
            $this->makeAttemptResult($exam1, $students[$sid], $scores1[$i], 50, now()->subDays(14));
        }

        // ── Exam 2: SE Principles Final (COMPLETED + published) ───────────────
        $exam2 = Exam::firstOrCreate(['title' => 'Software Engineering Principles Final'], [
            'course_id' => $se201->id, 'created_by' => $t2->id,
            'description'             => 'Final examination for SE201',
            'total_marks'             => 50, 'passing_marks' => 30,
            'duration_minutes'        => 120,
            'start_time'              => now()->subDays(7),
            'end_time'                => now()->subDays(7)->addHours(2),
            'status'                  => 'completed',
            'shuffle_questions'       => true,  'shuffle_options' => false,
            'allow_backtrack'         => true,  'show_result_immediately' => false,
            'instructions'            => 'Answer all questions. Total 10 MCQ × 5 marks.',
        ]);

        $this->attachQuestionsToExam($exam2, $bank2Qs, 5);

        $scores2 = [45, 40, 38, 42, 35, 28, 32, 44];
        $seSids  = ['SE-2021-001','SE-2021-002','SE-2021-003','SE-2021-004','SE-2022-001','SE-2022-002','SE-2022-001','SE-2022-002'];
        $seUniq  = ['SE-2021-001','SE-2021-002','SE-2021-003','SE-2021-004','SE-2022-001','SE-2022-002'];
        foreach ($seUniq as $i => $sid) {
            $this->makeAttemptResult($exam2, $students[$sid], $scores2[$i], 50, now()->subDays(7));
        }

        // ── Exam 3: OS Quiz 1 (ACTIVE) ────────────────────────────────────────
        Exam::firstOrCreate(['title' => 'Operating Systems Quiz 1'], [
            'course_id' => $cs401->id, 'created_by' => $t1->id,
            'total_marks' => 25, 'passing_marks' => 15, 'duration_minutes' => 45,
            'start_time' => now()->subHour(), 'end_time' => now()->addHours(2),
            'status' => 'active', 'shuffle_questions' => true, 'shuffle_options' => true,
            'allow_backtrack' => false, 'show_result_immediately' => true,
            'instructions' => '45 minutes. No backtracking. Attempt all questions.',
        ]);

        // ── Exam 4: Circuit Analysis Mid-Term (SCHEDULED) ─────────────────────
        Exam::firstOrCreate(['title' => 'Circuit Analysis Mid-Term'], [
            'course_id' => $ee201->id, 'created_by' => $t2->id,
            'total_marks' => 40, 'passing_marks' => 24, 'duration_minutes' => 80,
            'start_time' => now()->addDays(2)->setHour(10)->setMinute(0),
            'end_time'   => now()->addDays(2)->setHour(12)->setMinute(0),
            'status' => 'scheduled', 'allow_backtrack' => true, 'show_result_immediately' => false,
            'instructions' => 'Bring your student ID. 80-minute examination.',
        ]);

        // ── Exam 5: DSA Quiz 2 (DRAFT) ────────────────────────────────────────
        Exam::firstOrCreate(['title' => 'Data Structures & Algorithms Quiz 2'], [
            'course_id' => $cs201->id, 'created_by' => $t1->id,
            'total_marks' => 30, 'passing_marks' => 18, 'duration_minutes' => 60,
            'start_time' => now()->addDays(5), 'end_time' => now()->addDays(5)->addHours(2),
            'status' => 'draft', 'allow_backtrack' => true, 'show_result_immediately' => false,
        ]);

        // ── Audit Logs ────────────────────────────────────────────────────────
        $admin = User::where('email', 'admin@uniexam.local')->first();
        $logs  = [
            [$admin?->id,   'login',           'Admin logged in from 192.168.1.1'],
            [$t1->id,       'exam_created',    'Created "Database Systems Mid-Term" exam'],
            [$t1->id,       'result_publish',  'Published 6 results for Database Systems Mid-Term'],
            [$admin?->id,   'student_created', 'Added student Ahmed Raza (CS-2021-001)'],
            [$t2->id,       'exam_created',    'Created "SE Principles Final" exam'],
            [$admin?->id,   'user_updated',    'Assigned teacher role to Dr. Zubair Khan'],
            [$t2->id,       'result_publish',  'Published 6 results for SE Principles Final'],
            [$admin?->id,   'login',           'Admin session started'],
            [$t1->id,       'exam_created',    'Created "Operating Systems Quiz 1" (active)'],
            [$admin?->id,   'student_created', 'Bulk enrolled 14 students into courses'],
            [$t2->id,       'exam_created',    'Created "Circuit Analysis Mid-Term" (scheduled)'],
            [$admin?->id,   'faculty_created', 'Added Faculty of Business Administration'],
            [$t1->id,       'question_added',  'Added 10 MCQ questions to DB Systems bank'],
            [$t2->id,       'question_added',  'Added 10 MCQ questions to SE bank'],
            [$admin?->id,   'department_created','Added Electrical Engineering department'],
        ];

        foreach ($logs as $i => [$uid, $event, $desc]) {
            AuditLog::firstOrCreate(['description' => $desc], [
                'user_id'     => $uid,
                'event'       => $event,
                'description' => $desc,
                'ip_address'  => '192.168.1.' . ($i + 1),
                'user_agent'  => 'Mozilla/5.0 (Seeder)',
                'created_at'  => now()->subMinutes(rand(10, 2880)),
                'updated_at'  => now(),
            ]);
        }
    }

    private function makeMcqBank(int $bankId, array $questions): array
    {
        $created = [];
        foreach ($questions as [$text, $a, $b, $c, $d, $correct, $difficulty]) {
            $q = Question::firstOrCreate(
                ['question_bank_id' => $bankId, 'question_text' => $text],
                ['type' => 'mcq_single', 'marks' => 5, 'difficulty' => $difficulty, 'tags' => [], 'is_active' => true]
            );
            if ($q->options()->count() === 0) {
                foreach ([$a, $b, $c, $d] as $j => $opt) {
                    QuestionOption::create([
                        'question_id' => $q->id, 'option_text' => $opt,
                        'is_correct'  => ($j === $correct), 'order' => $j + 1,
                    ]);
                }
            }
            $created[] = $q;
        }
        return $created;
    }

    private function attachQuestionsToExam(Exam $exam, array $questions, float $marks): void
    {
        if ($exam->questions()->count() > 0) return;
        foreach ($questions as $i => $q) {
            DB::table('exam_questions')->insertOrIgnore([
                'exam_id' => $exam->id, 'question_id' => $q->id,
                'order' => $i + 1, 'marks' => $marks,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function makeAttemptResult(Exam $exam, Student $student, float $obtained, float $total, $date): void
    {
        if (ExamAttempt::where('exam_id', $exam->id)->where('student_id', $student->id)->exists()) return;

        $attempt = ExamAttempt::create([
            'exam_id'            => $exam->id,
            'student_id'         => $student->id,
            'started_at'         => $date,
            'submitted_at'       => $date->copy()->addMinutes(rand(45, 85)),
            'status'             => 'submitted',
            'ip_address'         => '192.168.1.' . rand(10, 200),
            'user_agent'         => 'Mozilla/5.0 (Windows NT 10.0)',
            'tab_switch_count'   => rand(0, 2),
            'time_spent_seconds' => rand(2700, 5000),
        ]);

        $pct = round(($obtained / $total) * 100, 2);
        [$grade, $gpa] = match(true) {
            $pct >= 90 => ['A+', 4.0],
            $pct >= 85 => ['A',  4.0],
            $pct >= 80 => ['B+', 3.5],
            $pct >= 75 => ['B',  3.0],
            $pct >= 70 => ['C+', 2.5],
            $pct >= 65 => ['C',  2.0],
            $pct >= 60 => ['D',  1.0],
            default    => ['F',  0.0],
        };

        Result::create([
            'attempt_id'      => $attempt->id,
            'student_id'      => $student->id,
            'exam_id'         => $exam->id,
            'total_marks'     => $total,
            'obtained_marks'  => $obtained,
            'percentage'      => $pct,
            'grade'           => $grade,
            'gpa'             => $gpa,
            'is_pass'         => $pct >= 60,
            'needs_evaluation'=> false,
            'evaluated_at'    => $date->copy()->addHours(3),
            'published_at'    => $date->copy()->addHours(24),
        ]);
    }
}
