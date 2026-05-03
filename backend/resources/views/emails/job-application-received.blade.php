<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <title>New Application Received</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #003366; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; color: #333; line-height: 1.6; }
        .highlight { background: #E6F0FF; border-left: 4px solid #003366; padding: 12px 16px; margin: 16px 0; border-radius: 4px; }
        .btn { display: inline-block; background: #003366; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { background: #f4f4f4; padding: 16px 32px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>New Job Application Received</h1>
    </div>
    <div class="body">
        <p>You have received a new application for the following position:</p>
        <div class="highlight">
            <strong>Job:</strong> {{ $job->title }}<br>
            <strong>Applicant:</strong> {{ $student->name }}<br>
            <strong>Applied At:</strong> {{ $application->applied_at->format('Y-m-d H:i') }} UTC
        </div>
        @if($application->cover_letter)
        <p><strong>Cover Letter:</strong></p>
        <p>{{ $application->cover_letter }}</p>
        @endif
        <p>Log in to your enterprise dashboard to review this application.</p>
        <a href="{{ $applicationsUrl }}" class="btn">View Application</a>
    </div>
    <div class="footer">
        This email was sent by HorizonHR. Please do not reply directly to this email.
    </div>
</div>
</body>
</html>
