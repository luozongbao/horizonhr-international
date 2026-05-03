<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seminar Reminder</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,'Microsoft YaHei',sans-serif;background-color:#F5F7FA;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F5F7FA;">
    <tr>
        <td align="center" style="padding:40px 20px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#FFFFFF;border-radius:12px;box-shadow:0 4px 20px rgba(0,51,102,0.1);">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(135deg,#003366 0%,#004080 100%);padding:32px;text-align:center;border-radius:12px 12px 0 0;">
                        <h1 style="margin:0;font-size:28px;color:#FFFFFF;font-weight:700;">HBHR</h1>
                        <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.8);">Horizon International HR Platform</p>
                    </td>
                </tr>

                <!-- Reminder Banner -->
                <tr>
                    <td style="background:#FFF3E6;padding:16px 32px;text-align:center;">
                        <p style="margin:0;font-size:14px;color:#FF6B35;font-weight:600;">
                            🔔 Starting in {{ $minutesUntilStart }} minutes!
                        </p>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:40px 32px;">
                        <p style="margin:0 0 8px;font-size:14px;color:#6C757D;">Dear {{ $registration->name }},</p>
                        <h2 style="margin:0 0 24px;font-size:24px;color:#003366;font-weight:600;">
                            Your seminar is about to start!
                        </h2>

                        <!-- Seminar Card -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:2px solid #E6F0FF;border-radius:16px;">
                            <tr>
                                <td style="padding:28px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td width="100" valign="middle" style="padding-right:20px;">
                                                <div style="width:100px;background:linear-gradient(135deg,#003366 0%,#004080 100%);border-radius:12px;padding:12px 0;text-align:center;color:#FFFFFF;">
                                                    <div style="font-size:32px;font-weight:700;line-height:1;">{{ $seminarDay }}</div>
                                                    <div style="font-size:14px;">{{ $seminarMonth }}</div>
                                                </div>
                                            </td>
                                            <td valign="middle">
                                                <h3 style="margin:0 0 8px;font-size:22px;color:#003366;font-weight:600;">
                                                    @if(($lang ?? 'en') === 'zh_cn')
                                                        {{ $seminar->title_zh_cn ?? $seminar->title_en }}
                                                    @else
                                                        {{ $seminar->title_en ?? $seminar->title_zh_cn }}
                                                    @endif
                                                </h3>
                                                <p style="margin:0;font-size:14px;color:#6C757D;">
                                                    🕐 {{ $seminarTime }}&nbsp;&nbsp;|&nbsp;&nbsp;📍 Online
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:20px;border-top:1px solid #E6F0FF;padding-top:20px;">
                                        <tr>
                                            <td>
                                                @if($seminar->speaker_name)
                                                <p style="margin:0 0 4px;font-size:12px;color:#6C757D;">🎤 Speaker:</p>
                                                <p style="margin:0;font-size:15px;color:#1A1A2E;font-weight:500;">{{ $seminar->speaker_name }}</p>
                                                @if($seminar->speaker_title)
                                                <p style="margin:4px 0 0;font-size:12px;color:#6C757D;">{{ $seminar->speaker_title }}</p>
                                                @endif
                                                @endif
                                            </td>
                                            <td align="right">
                                                <p style="margin:0 0 4px;font-size:12px;color:#6C757D;">👥 Registered:</p>
                                                <p style="margin:0;font-size:20px;color:#003366;font-weight:700;">{{ $registrationCount }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Countdown -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;">
                            <tr>
                                <td style="background:#E6F0FF;border-radius:12px;padding:24px;text-align:center;">
                                    <p style="margin:0 0 8px;font-size:14px;color:#003366;">⏰ Seminar starts in</p>
                                    <p style="margin:0;font-size:48px;font-weight:700;color:#003366;line-height:1;">{{ $minutesUntilStart }}</p>
                                    <p style="margin:4px 0 0;font-size:16px;color:#003366;">minutes</p>
                                </td>
                            </tr>
                        </table>

                        <!-- CTA Button -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $watchUrl }}"
                                       style="display:inline-block;padding:16px 48px;background-color:#FF6B35;color:#FFFFFF;text-decoration:none;border-radius:8px;font-size:16px;font-weight:600;">
                                        Watch Live Now
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:24px 0 0;font-size:13px;color:#6C757D;text-align:center;">
                            This reminder was sent because you registered for this seminar with {{ $registration->email }}.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#F8F9FA;padding:24px 32px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #E9ECEF;">
                        <p style="margin:0;font-size:12px;color:#6C757D;">
                            © {{ date('Y') }} HBHR — Horizon International HR Platform
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
