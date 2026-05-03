<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email — HorizonHR</title>
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
                            <h2 style="margin: 0 0 24px; font-size: 24px; color: #003366; font-weight: 600;">
                                Hello, {{ $user->student->name ?? $user->enterprise->company_name ?? $user->email }}!
                            </h2>

                            <p style="margin: 0 0 20px; font-size: 16px; color: #1A1A2E; line-height: 1.6;">
                                Thank you for registering with HorizonHR. Please verify your email address to activate your account and start exploring opportunities.
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 32px 0;">
                                <a href="{{ $link }}" style="display: inline-block; padding: 16px 48px; background: #FF6B35; color: #FFFFFF; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                    Verify Email Address
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

                            <p style="margin: 0; font-size: 14px; color: #6C757D; line-height: 1.6;">
                                This verification link expires in <strong>24 hours</strong>. If you did not create an account with HorizonHR, please ignore this email.
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

                <!-- Security Notice -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; margin-top: 24px;">
                    <tr>
                        <td style="padding: 0 16px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #6C757D; line-height: 1.6;">
                                This is an automated message. Please do not reply to this email.
                                If you need assistance, contact our support team.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

