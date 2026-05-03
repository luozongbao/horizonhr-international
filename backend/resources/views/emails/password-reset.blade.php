<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset — HorizonHR</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, 'Microsoft YaHei', sans-serif; background-color: #F5F7FA;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F7FA;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Email Container -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #003366 0%, #004080 100%); padding: 32px; text-align: center; border-radius: 12px 12px 0 0;">
                            <h1 style="margin: 0; font-size: 28px; color: #FFFFFF; font-weight: 700;">HorizonHR</h1>
                            <p style="margin: 8px 0 0; font-size: 14px; color: rgba(255,255,255,0.8);">Connecting Talent with Opportunity</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 32px;">
                            <!-- Key Icon -->
                            <div style="text-align: center; margin-bottom: 32px;">
                                <div style="display: inline-block; width: 80px; height: 80px; background: #FFF3E6; border-radius: 50%; text-align: center; line-height: 80px;">
                                    <span style="font-size: 40px;">&#128273;</span>
                                </div>
                            </div>

                            <h2 style="margin: 0 0 16px; font-size: 24px; color: #003366; font-weight: 600; text-align: center;">
                                Reset Your Password
                            </h2>

                            <p style="margin: 0 0 24px; font-size: 16px; color: #1A1A2E; line-height: 1.6; text-align: center;">
                                Hello {{ $user->student->name ?? $user->enterprise->company_name ?? $user->email }},<br>
                                we received a request to reset your HorizonHR account password.
                                Click the button below to choose a new password.
                            </p>

                            <!-- Reset Button -->
                            <div style="text-align: center; margin: 32px 0;">
                                <a href="{{ $link }}" style="display: inline-block; padding: 16px 48px; background: #FF6B35; color: #FFFFFF; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                    Reset Password
                                </a>
                            </div>

                            <!-- Alternative Link -->
                            <div style="background: #F5F7FA; border-radius: 8px; padding: 20px; margin: 24px 0;">
                                <p style="margin: 0 0 8px; font-size: 13px; color: #6C757D; text-align: center;">
                                    Or copy and paste this link into your browser:
                                </p>
                                <p style="margin: 0; font-size: 13px; color: #003366; word-break: break-all; text-align: center;">
                                    <a href="{{ $link }}" style="color: #003366;">{{ $link }}</a>
                                </p>
                            </div>

                            <!-- Expiry Warning -->
                            <div style="background: #FFE6E6; border-radius: 8px; padding: 16px; margin: 24px 0; border-left: 4px solid #DC3545;">
                                <p style="margin: 0; font-size: 14px; color: #DC3545; font-weight: 500;">
                                    &#9200; This link expires in <strong>2 hours</strong>. Please reset your password promptly.
                                </p>
                            </div>

                            <hr style="border: none; border-top: 1px solid #DEE2E6; margin: 32px 0;">

                            <p style="margin: 0; font-size: 13px; color: #6C757D; line-height: 1.6; text-align: center;">
                                If you did not request a password reset, you can safely ignore this email.
                                Your password will not be changed.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #003366; padding: 24px 32px; text-align: center; border-radius: 0 0 12px 12px;">
                            <p style="margin: 0 0 8px; font-size: 14px; color: rgba(255,255,255,0.9);">
                                HorizonHR International Recruitment Platform
                            </p>
                            <p style="margin: 16px 0 0; font-size: 12px; color: rgba(255,255,255,0.5);">
                                &copy; {{ date('Y') }} HorizonHR. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

