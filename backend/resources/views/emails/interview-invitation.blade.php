<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interview Invitation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #003366; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; color: #333; line-height: 1.6; }
        .highlight { background: #E6F0FF; border-left: 4px solid #003366; padding: 12px 16px; margin: 16px 0; border-radius: 4px; }
        .btn { display: inline-block; background: #FF6B35; color: #fff; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 20px; font-size: 16px; }
        .footer { background: #f4f4f4; padding: 16px 32px; font-size: 12px; color: #888; }
        .note { color: #888; font-size: 13px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>You Have Been Invited to an Interview</h1>
    </div>
    <div class="body">
        <p>Dear {{ $student->name }},</p>
        <p>You have been invited to participate in an online interview. Please review the details below:</p>
        <div class="highlight">
            <strong>Interview:</strong> {{ $interview->title }}<br>
            <strong>Scheduled At:</strong> {{ $interview->scheduled_at->format('Y-m-d H:i') }} UTC<br>
            <strong>Duration:</strong> {{ $interview->duration ?? 30 }} minutes
        </div>
        <p>To join the interview, click the button below. You do <strong>not</strong> need to log in — the link contains your secure access token.</p>
        <a href="{{ $joinUrl }}" class="btn">Join Interview</a>
        <p class="note">This link is valid for 48 hours. If the link has expired, please contact the employer to request a new one.</p>
        <p class="note">Join link: <a href="{{ $joinUrl }}">{{ $joinUrl }}</a></p>
    </div>
    <div class="footer">
        This email was sent by HorizonHR. Please do not reply directly to this email.
    </div>
</div>
</body>
</html>
