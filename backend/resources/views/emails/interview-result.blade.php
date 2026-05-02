<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interview Result</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #003366; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; color: #333; line-height: 1.6; }
        .highlight { background: #E6F0FF; border-left: 4px solid #003366; padding: 12px 16px; margin: 16px 0; border-radius: 4px; }
        .result-pass    { color: #16a34a; font-weight: bold; }
        .result-fail    { color: #dc2626; font-weight: bold; }
        .result-pending { color: #d97706; font-weight: bold; }
        .result-no_show { color: #6b7280; font-weight: bold; }
        .btn { display: inline-block; background: #003366; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { background: #f4f4f4; padding: 16px 32px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Interview Result Update</h1>
    </div>
    <div class="body">
        <p>Dear {{ $student->name }},</p>
        <p>The result for your recent interview has been updated:</p>
        <div class="highlight">
            <strong>Interview:</strong> {{ $interview->title }}<br>
            <strong>Result:</strong>
            <span class="result-{{ $record->result }}">{{ ucfirst(str_replace('_', ' ', $record->result ?? 'N/A')) }}</span>
            @if($record->rating)
            <br><strong>Rating:</strong> {{ $record->rating }} / 5
            @endif
        </div>
        @if($record->notes)
        <p><strong>Feedback from interviewer:</strong></p>
        <p>{{ $record->notes }}</p>
        @endif
        @if($record->result === 'pass')
        <p>Congratulations! You have passed the interview. The interviewer may reach out to you with next steps.</p>
        @elseif($record->result === 'fail')
        <p>Thank you for participating. Unfortunately, you were not selected to move forward at this time. Keep applying — new opportunities are posted regularly.</p>
        @endif
        <a href="{{ $dashboardUrl }}" class="btn">View My Interviews</a>
    </div>
    <div class="footer">
        This email was sent by HorizonHR. Please do not reply directly to this email.
    </div>
</div>
</body>
</html>
