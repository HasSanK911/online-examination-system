<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
  .header { background: #4f46e5; color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
  .header h1 { font-size: 22px; font-weight: bold; }
  .header p { font-size: 11px; opacity: 0.85; margin-top: 3px; }
  .badge { background: rgba(255,255,255,0.2); border-radius: 6px; padding: 4px 12px; font-size: 11px; }
  .body { padding: 24px 30px; }
  .section { margin-bottom: 20px; }
  .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px; }
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .field label { font-size: 10px; color: #94a3b8; display: block; margin-bottom: 2px; }
  .field span { font-size: 12px; color: #1e293b; font-weight: 500; }
  .result-box { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 20px; }
  .marks { font-size: 36px; font-weight: bold; color: #4f46e5; }
  .grade-badge { display: inline-block; padding: 6px 20px; border-radius: 9999px; font-size: 18px; font-weight: bold; margin: 8px 0; }
  .pass { background: #dcfce7; color: #166534; }
  .fail { background: #fee2e2; color: #991b1b; }
  .rank-row { display: flex; justify-content: space-around; margin-top: 12px; }
  .rank-item { text-align: center; }
  .rank-num { font-size: 22px; font-weight: bold; color: #4f46e5; }
  .rank-label { font-size: 10px; color: #64748b; }
  .footer { background: #f1f5f9; padding: 12px 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  th { background: #f8fafc; text-align: left; padding: 8px 12px; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
  td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
  .correct { color: #16a34a; font-weight: 600; }
  .incorrect { color: #dc2626; font-weight: 600; }
</style>
</head>
<body>
<div class="header">
  <div>
    <h1>Result Card</h1>
    <p>{{ $result->student->department->faculty->name ?? 'University' }}</p>
    <p>{{ $result->student->department->name ?? 'Department' }}</p>
  </div>
  <div style="text-align:right">
    <div class="badge">{{ $result->exam->course->code }}</div>
    <p style="margin-top:6px; font-size:11px">{{ $result->published_at?->format('M d, Y') }}</p>
  </div>
</div>

<div class="body">
  <!-- Student Info -->
  <div class="section">
    <div class="section-title">Student Information</div>
    <div class="grid-2">
      <div class="field"><label>Full Name</label><span>{{ $result->student->user->name }}</span></div>
      <div class="field"><label>Student ID</label><span>{{ $result->student->student_id }}</span></div>
      <div class="field"><label>Roll Number</label><span>{{ $result->student->roll_number }}</span></div>
      <div class="field"><label>Semester</label><span>{{ $result->student->semester }}</span></div>
      <div class="field"><label>Batch</label><span>{{ $result->student->batch ?? '—' }}</span></div>
      <div class="field"><label>Department</label><span>{{ $result->student->department->name }}</span></div>
    </div>
  </div>

  <!-- Exam Info -->
  <div class="section">
    <div class="section-title">Examination Details</div>
    <div class="grid-2">
      <div class="field"><label>Exam Title</label><span>{{ $result->exam->title }}</span></div>
      <div class="field"><label>Course</label><span>{{ $result->exam->course->title }} ({{ $result->exam->course->code }})</span></div>
      <div class="field"><label>Total Marks</label><span>{{ $result->total_marks }}</span></div>
      <div class="field"><label>Passing Marks</label><span>{{ $result->exam->passing_marks }}</span></div>
    </div>
  </div>

  <!-- Result Summary -->
  <div class="result-box">
    <div class="marks">{{ $result->obtained_marks }} / {{ $result->total_marks }}</div>
    <div style="color:#64748b; font-size:13px; margin: 4px 0">{{ $result->percentage }}%</div>
    <span class="grade-badge {{ $result->is_pass ? 'pass' : 'fail' }}">
      Grade: {{ $result->grade }} &nbsp;|&nbsp; GPA: {{ number_format($result->gpa, 2) }}
    </span>
    <div style="margin-top:6px; font-size:14px; font-weight:600; color: {{ $result->is_pass ? '#166534' : '#991b1b' }}">
      {{ $result->is_pass ? '✓ PASS' : '✗ FAIL' }}
    </div>
    <div class="rank-row">
      @if($result->class_rank)
      <div class="rank-item">
        <div class="rank-num">#{{ $result->class_rank }}</div>
        <div class="rank-label">Class Rank</div>
      </div>
      @endif
      @if($result->department_rank)
      <div class="rank-item">
        <div class="rank-num">#{{ $result->department_rank }}</div>
        <div class="rank-label">Department Rank</div>
      </div>
      @endif
      @if($result->semester_rank)
      <div class="rank-item">
        <div class="rank-num">#{{ $result->semester_rank }}</div>
        <div class="rank-label">Semester Rank</div>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="footer">
  Generated by UniExam &bull; This document is system-generated and does not require a signature.
</div>
</body>
</html>
