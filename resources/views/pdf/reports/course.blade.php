<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
    h1 { font-size: 18px; font-weight: 700; color: #BC2739; margin: 0; }
    h2 { font-size: 13px; font-weight: 700; color: #334155; margin: 16px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    .header { border-bottom: 2px solid #BC2739; padding-bottom: 12px; margin-bottom: 16px; }
    .subtitle { color: #64748b; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th { background: #f8fafc; text-align: left; padding: 7px 10px; font-size: 9px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
    .pass { background: #dcfce7; color: #166534; }
    .fail { background: #fee2e2; color: #991b1b; }
    .summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px; display: table; width: 100%; }
    .summary-cell { display: table-cell; text-align: center; }
    .summary-num { font-size: 20px; font-weight: 700; color: #BC2739; }
    .info-label { color: #64748b; font-size: 9px; font-weight: 600; text-transform: uppercase; }
    .footer { text-align: center; color: #94a3b8; font-size: 9px; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>
<div class="header">
    <h1>University Online Examination System</h1>
    <div class="subtitle">Course Performance Report &mdash; Generated {{ now()->format('d M Y') }}</div>
</div>

<h2>{{ $course }} <span style="font-weight:normal;color:#64748b;">({{ $code }})</span></h2>

<div class="summary">
    <div class="summary-cell"><div class="summary-num">{{ $summary['total'] }}</div><div class="info-label">Results</div></div>
    <div class="summary-cell"><div class="summary-num" style="color:#16a34a">{{ $summary['passed'] }}</div><div class="info-label">Passed</div></div>
    <div class="summary-cell"><div class="summary-num" style="color:#dc2626">{{ $summary['failed'] }}</div><div class="info-label">Failed</div></div>
    <div class="summary-cell"><div class="summary-num">{{ $summary['avg'] }}%</div><div class="info-label">Average</div></div>
    <div class="summary-cell"><div class="summary-num">{{ $summary['highest'] }}%</div><div class="info-label">Highest</div></div>
    <div class="summary-cell"><div class="summary-num">{{ $summary['lowest'] }}%</div><div class="info-label">Lowest</div></div>
</div>

<h2>Student Results</h2>
<table>
    <thead>
        <tr>
            <th>Student</th>
            <th>Exam</th>
            <th>Marks</th>
            <th>%</th>
            <th>Grade</th>
            <th>Rank</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $r)
        <tr>
            <td>{{ $r['student_name'] }}<br><small style="color:#94a3b8">{{ $r['student_code'] }}</small></td>
            <td>{{ $r['exam_title'] }}</td>
            <td>{{ $r['obtained_marks'] }} / {{ $r['total_marks'] }}</td>
            <td>{{ $r['percentage'] }}%</td>
            <td><b>{{ $r['grade'] }}</b></td>
            <td>{{ $r['class_rank'] ?? '—' }}</td>
            <td><span class="badge {{ $r['is_pass'] ? 'pass' : 'fail' }}">{{ $r['is_pass'] ? 'Pass' : 'Fail' }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">University Online Examination System &bull; Confidential &bull; {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
