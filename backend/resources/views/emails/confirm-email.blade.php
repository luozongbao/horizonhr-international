<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Verify Your Email — HorizonHR</title></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:40px 0;">
<table width="600" align="center" style="background:#fff;border-radius:8px;padding:40px;">
  <tr><td style="text-align:center;padding-bottom:24px;">
    <h1 style="color:#003366;margin:0;">HorizonHR</h1>
  </td></tr>
  <tr><td>
    <h2 style="color:#1A1A2E;">Verify Your Email Address</h2>
    <p>Hello {{ $user->student->name ?? $user->enterprise->company_name ?? $user->email }},</p>
    <p>Thank you for registering. Please click the button below to verify your email address and activate your account.</p>
    <p style="text-align:center;margin:32px 0;">
      <a href="{{ $link }}" style="background:#003366;color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;">Verify Email</a>
    </p>
    <p style="color:#6C757D;font-size:13px;">This link expires in 24 hours. If you did not create an account, please ignore this email.</p>
    <p style="color:#6C757D;font-size:12px;">Or copy this link: <a href="{{ $link }}">{{ $link }}</a></p>
  </td></tr>
</table>
</body>
</html>
