<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Password Reset — HorizonHR</title></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:40px 0;">
<table width="600" align="center" style="background:#fff;border-radius:8px;padding:40px;">
  <tr><td style="text-align:center;padding-bottom:24px;">
    <h1 style="color:#003366;margin:0;">HorizonHR</h1>
  </td></tr>
  <tr><td>
    <h2 style="color:#1A1A2E;">Reset Your Password</h2>
    <p>Hello {{ $user->email }},</p>
    <p>We received a request to reset your password. Click the button below to choose a new password.</p>
    <p style="text-align:center;margin:32px 0;">
      <a href="{{ $link }}" style="background:#003366;color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;">Reset Password</a>
    </p>
    <p style="color:#6C757D;font-size:13px;">This link expires in 2 hours. If you did not request a password reset, you can safely ignore this email.</p>
    <p style="color:#6C757D;font-size:12px;">Or copy this link: <a href="{{ $link }}">{{ $link }}</a></p>
  </td></tr>
</table>
</body>
</html>
