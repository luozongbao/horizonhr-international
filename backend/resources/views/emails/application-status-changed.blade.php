<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #003366; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; color: #333; line-height: 1.6; }
        .highlight { background: #E6F0FF; border-left: 4px solid #003366; padding: 12px 16px; margin: 16px 0; border-radius: 4px; }
        .status-accepted { color: #16a34a; font-weight: bold; }
        .status-rejected { color: #dc2626; font-weight: bold; }
        .btn { display: inline-block; background: #003366; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { background: #f4f4f4; padding: 16px 32px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Application Status Update</h1>
    </div>
    <div class="body">
        <p>There is an update on your job application:</p>
        <div class="highlight">
            <strong>Job:</strong> {{ $job->title }}<br>
            <strong>Company:</strong> {{ $enterprise->company_name }}<br>
            <strong>New Status:</strong>
            <span class="status-{{ $application->status }}">{{ ucfirst($application->status) }}</span>
        </div>
        @if($application->notes)
        <p><strong>Message from the employer:</strong></p>
        <p>{{ $application->notes }}</p>
        @endif
        @if($application->status === 'accepted')
        <p>Congratulations! The employer has accepted your application. They may reach out to you soon to discuss next steps.</p>
        @elseif($application->status === 'rejected')
        <p>Thank you for applying. Unfortunately, the employer has decided not to move forward with your application at this time. Keep applying — new opportunities are added regularly!</p>
        @endif
        <a href="{{ $applicationsUrl }}" class="btn">View My Applications</a>
    </div>
    <div class="footer">
        This email was sent by HorizonHR. Please do not reply directly to this email.
    </div>
</div>
</body>
</html>
