<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Enterprise Account Has Been Activated — HorizonHR</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#F5F7FA;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F5F7FA;">
  <tr><td align="center" style="padding:40px 20px;">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
           style="max-width:600px;width:100%;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,51,102,0.1);">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#003366 0%,#004080 100%);padding:32px;text-align:center;border-radius:12px 12px 0 0;">
        <h1 style="margin:0;font-size:28px;color:#fff;font-weight:700;">HorizonHR</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.8);">International Recruitment Platform</p>
      </td></tr>
      <!-- Success Banner -->
      <tr><td style="background:#E6F7ED;padding:24px 32px;text-align:center;">
        <div style="display:inline-block;width:64px;height:64px;background:#28A745;border-radius:50%;text-align:center;line-height:64px;margin-bottom:16px;">
          <span style="font-size:32px;color:#fff;">✓</span>
        </div>
        <p style="margin:0;font-size:18px;color:#28A745;font-weight:600;">Account Activated Successfully!</p>
      </td></tr>
      <!-- Content -->
      <tr><td style="padding:40px 32px;">
        <h2 style="margin:0 0 24px;font-size:24px;color:#003366;">Dear {{ $enterprise->contact_name ?? $enterprise->company_name ?? $user->email }},</h2>
        <p style="margin:0 0 20px;font-size:16px;color:#1A1A2E;line-height:1.6;">
          Congratulations! Your enterprise account has been reviewed and approved. You can now access all features of HorizonHR.
        </p>
        <!-- Company Card -->
        <div style="background:#F5F7FA;border-radius:12px;padding:24px;margin:24px 0;text-align:center;">
          <div style="display:inline-block;width:80px;height:80px;background:#003366;border-radius:12px;line-height:80px;font-size:36px;color:#fff;margin-bottom:16px;">
            {{ strtoupper(substr($enterprise->company_name ?? 'E', 0, 1)) }}
          </div>
          <h3 style="margin:0 0 8px;font-size:22px;color:#003366;">{{ $enterprise->company_name }}</h3>
          <p style="margin:0;font-size:14px;color:#28A745;font-weight:500;">✓ Verified Enterprise</p>
        </div>
        <!-- Features -->
        <div style="margin:32px 0;">
          <p style="margin:0 0 16px;font-size:16px;color:#1A1A2E;font-weight:600;">What you can do now:</p>
          <ul style="margin:0;padding:0;list-style:none;">
            <li style="padding:12px 0;border-bottom:1px solid #E6F0FF;color:#1A1A2E;">📝 Post job openings and reach international talent</li>
            <li style="padding:12px 0;border-bottom:1px solid #E6F0FF;color:#1A1A2E;">🔍 Search and shortlist qualified candidates</li>
            <li style="padding:12px 0;color:#1A1A2E;">📹 Schedule and conduct video interviews</li>
          </ul>
        </div>
        <!-- CTA -->
        <div style="text-align:center;margin:32px 0;">
          <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:16px 48px;background:#FF6B35;color:#fff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:600;">
            Go to Dashboard
          </a>
        </div>
        <p style="margin:0;font-size:14px;color:#6C757D;line-height:1.6;">
          Welcome to HorizonHR. We look forward to helping you find the right talent.
        </p>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#003366;padding:24px 32px;text-align:center;border-radius:0 0 12px 12px;">
        <p style="margin:0 0 8px;font-size:14px;color:rgba(255,255,255,0.9);">HorizonHR International Recruitment Platform</p>
        <p style="margin:16px 0 0;font-size:12px;color:rgba(255,255,255,0.5);">© {{ date('Y') }} HorizonHR. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
