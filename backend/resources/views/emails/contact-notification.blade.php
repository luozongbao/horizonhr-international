<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#F5F7FA;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F5F7FA;">
    <tr>
        <td align="center" style="padding:40px 20px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                   style="max-width:600px;width:100%;background-color:#FFFFFF;border-radius:12px;box-shadow:0 4px 20px rgba(0,51,102,0.1);">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(135deg,#003366 0%,#004080 100%);padding:28px 32px;text-align:center;border-radius:12px 12px 0 0;">
                        <h1 style="margin:0;font-size:22px;color:#FFFFFF;font-weight:700;">New Contact Form Submission</h1>
                        <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.75);">HBHR Admin Notification</p>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:36px 32px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                               style="border:1px solid #E6F0FF;border-radius:10px;overflow:hidden;">
                            <tr>
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;width:120px;">
                                    Name
                                </td>
                                <td style="padding:12px 20px;font-size:14px;color:#1A1A2E;">{{ $contact->name }}</td>
                            </tr>
                            <tr style="border-top:1px solid #E6F0FF;">
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">
                                    Email
                                </td>
                                <td style="padding:12px 20px;font-size:14px;color:#1A1A2E;">
                                    <a href="mailto:{{ $contact->email }}" style="color:#003366;">{{ $contact->email }}</a>
                                </td>
                            </tr>
                            @if($contact->phone)
                            <tr style="border-top:1px solid #E6F0FF;">
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">
                                    Phone
                                </td>
                                <td style="padding:12px 20px;font-size:14px;color:#1A1A2E;">{{ $contact->phone }}</td>
                            </tr>
                            @endif
                            <tr style="border-top:1px solid #E6F0FF;">
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">
                                    Subject
                                </td>
                                <td style="padding:12px 20px;font-size:14px;color:#1A1A2E;font-weight:600;">{{ $contact->subject }}</td>
                            </tr>
                            <tr style="border-top:1px solid #E6F0FF;">
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;vertical-align:top;">
                                    Message
                                </td>
                                <td style="padding:12px 20px;font-size:14px;color:#1A1A2E;line-height:1.6;">
                                    {!! nl2br(e($contact->message)) !!}
                                </td>
                            </tr>
                            <tr style="border-top:1px solid #E6F0FF;">
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">
                                    IP Address
                                </td>
                                <td style="padding:12px 20px;font-size:13px;color:#6C757D;">{{ $contact->ip_address }}</td>
                            </tr>
                            <tr style="border-top:1px solid #E6F0FF;">
                                <td style="background:#F0F5FF;padding:12px 20px;font-size:12px;font-weight:600;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">
                                    Submitted
                                </td>
                                <td style="padding:12px 20px;font-size:13px;color:#6C757D;">{{ $contact->created_at->format('Y-m-d H:i') }} UTC</td>
                            </tr>
                        </table>

                        <!-- CTA -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:28px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $adminUrl }}"
                                       style="display:inline-block;padding:14px 40px;background-color:#FF6B35;color:#FFFFFF;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;">
                                        View in Admin Panel
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#F8F9FA;padding:20px 32px;text-align:center;border-radius:0 0 12px 12px;border-top:1px solid #E9ECEF;">
                        <p style="margin:0;font-size:12px;color:#6C757D;">
                            © {{ date('Y') }} HBHR — This is an automated admin notification.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
