<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Enterprise Registration Pending Review — HorizonHR</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#F5F7FA;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F5F7FA;">
  <tr><td align="center" style="padding:40px 20px;">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
           style="max-width:600px;width:100%;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,51,102,0.1);">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#003366 0%,#004080 100%);padding:32px;text-align:center;border-radius:12px 12px 0 0;">
        <h1 style="margin:0;font-size:28px;color:#fff;font-weight:700;">HorizonHR</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.8);">Admin Notification</p>
      </td></tr>
      <!-- Alert Banner -->
      <tr><td style="background:#FFF3E6;padding:20px 32px;text-align:center;border-left:4px solid #FF6B35;">
        <p style="margin:0;font-size:14px;color:#FF6B35;font-weight:600;">⚠️ Action Required: New Enterprise Account Pending Review</p>
      </td></tr>
      <!-- Content -->
      <tr><td style="padding:40px 32px;">
        <h2 style="margin:0 0 24px;font-size:24px;color:#003366;">Hello Admin,</h2>
        <p style="margin:0 0 16px;font-size:16px;color:#1A1A2E;line-height:1.6;">
          A new enterprise account has completed email verification and is awaiting your review and approval.
        </p>
        <!-- Details Box -->
        <div style="background:#F5F7FA;border-radius:12px;padding:24px;margin:24px 0;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;width:40%;">
                <strong style="color:#003366;">Company Name:</strong>
              </td>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <span style="color:#1A1A2E;">{{ $enterprise->company_name ?? 'N/A' }}</span>
              </td>
            </tr>
            <tr>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <strong style="color:#003366;">Contact Person:</strong>
              </td>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <span style="color:#1A1A2E;">{{ $enterprise->contact_name ?? 'N/A' }}</span>
              </td>
            </tr>
            <tr>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <strong style="color:#003366;">Email:</strong>
              </td>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <span style="color:#1A1A2E;">{{ $user->email }}</span>
              </td>
            </tr>
            <tr>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <strong style="color:#003366;">Industry:</strong>
              </td>
              <td style="padding:8px 0;border-bottom:1px solid #DEE2E6;">
                <span style="color:#1A1A2E;">{{ $enterprise->industry ?? 'N/A' }}</span>
              </td>
            </tr>
            <tr>
              <td style="padding:8px 0;">
                <strong style="color:#003366;">Phone:</strong>
              </td>
              <td style="padding:8px 0;">
                <span style="color:#1A1A2E;">{{ $enterprise->contact_phone ?? 'N/A' }}</span>
              </td>
            </tr>
          </table>
        </div>
        <p style="margin:0 0 24px;font-size:14px;color:#6C757D;line-height:1.6;">
          Please review the enterprise details and activate the account if everything looks correct.
        </p>
        <!-- CTA -->
        <div style="text-align:center;margin:32px 0;">
          <a href="{{ $adminReviewUrl }}" style="display:inline-block;padding:16px 48px;background:#003366;color:#fff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:600;">
            Review Enterprise Account
          </a>
        </div>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#003366;padding:24px 32px;text-align:center;border-radius:0 0 12px 12px;">
        <p style="margin:0 0 8px;font-size:14px;color:rgba(255,255,255,0.9);">HorizonHR Admin Panel</p>
        <p style="margin:16px 0 0;font-size:12px;color:rgba(255,255,255,0.5);">© {{ date('Y') }} HorizonHR. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
