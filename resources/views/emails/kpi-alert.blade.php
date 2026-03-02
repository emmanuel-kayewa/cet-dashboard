<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>ZESCO Dashboard — KPI Alert</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        :root { color-scheme: light dark; supported-color-schemes: light dark; }
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        @media (prefers-color-scheme: dark) {
            .email-body { background-color: #0a0a0a !important; }
            .email-card { background-color: #141414 !important; border-color: #262626 !important; }
            .email-heading { color: #ffffff !important; }
            .email-subtext { color: #a3a3a3 !important; }
            .email-muted { color: #737373 !important; }
            .email-divider { border-color: #262626 !important; }
            .email-btn { background-color: #ffffff !important; color: #000000 !important; }
            .email-footer-text { color: #525252 !important; }
            .email-logo-light { display: none !important; }
            .email-logo-dark { display: inline-block !important; max-width: 120px !important; height: auto !important; }
            .severity-badge { border-color: #333 !important; }
            .detail-label { color: #999 !important; }
            .detail-value { color: #ddd !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" class="email-body" style="background-color: #fafafa; padding: 48px 16px;">

                <!-- Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 520px;">
                    <tr>
                        <td class="email-card" style="background-color: #ffffff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 48px 40px;">

                            <!-- Logo -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <img class="email-logo-light" src="{{ url('/images/zesco_black_logo.svg') }}" alt="ZESCO" width="120" style="display: inline-block; max-width: 120px; height: auto;" />
                                        <img class="email-logo-dark" src="{{ url('/images/zesco_white_logo.svg') }}" alt="ZESCO" width="120" style="display: none; max-width: 0; height: 0; overflow: hidden;" />
                                    </td>
                                </tr>
                            </table>

                            <!-- Severity Badge -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 20px;">
                                        @php
                                            $badgeColor = match($alert->severity) {
                                                'critical' => '#dc2626',
                                                'warning' => '#d97706',
                                                default => '#2563eb',
                                            };
                                            $badgeText = strtoupper($alert->severity);
                                            $typeLabel = str_replace('_', ' ', strtoupper($alert->type));
                                        @endphp
                                        <span class="severity-badge" style="display: inline-block; background-color: {{ $badgeColor }}; color: #ffffff; padding: 4px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">
                                            {{ $badgeText }} — {{ $typeLabel }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="email-divider" style="border-bottom: 1px solid #e5e5e5; padding-bottom: 0; line-height: 1px; font-size: 1px;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Alert Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top: 28px;">
                                        <h1 class="email-heading" style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #0a0a0a; letter-spacing: -0.025em;">
                                            {{ $alert->title }}
                                        </h1>
                                        <p class="email-subtext" style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.7; color: #525252;">
                                            Hello{{ $user->name ? ' ' . explode(' ', $user->name)[0] : '' }}, the following alert was triggered on the ZESCO Executive Dashboard:
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alert Message Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                                            <tr>
                                                <td style="padding: 20px; border-left: 4px solid {{ $badgeColor }}; border-radius: 8px;">
                                                    <p class="email-subtext" style="margin: 0; font-size: 14px; line-height: 1.7; color: #374151;">
                                                        {{ $alert->message }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alert Details -->
                            @if($alert->metadata)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        @if(isset($alert->metadata['current_value']) && isset($alert->metadata['target_value']))
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 13px;">
                                            <tr>
                                                <td class="detail-label" style="padding: 6px 0; color: #6b7280; width: 45%;">Current Value</td>
                                                <td class="detail-value" style="padding: 6px 0; color: #111827; font-weight: 600;">{{ $alert->metadata['current_value'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="detail-label" style="padding: 6px 0; color: #6b7280;">Target Value</td>
                                                <td class="detail-value" style="padding: 6px 0; color: #111827; font-weight: 600;">{{ $alert->metadata['target_value'] }}</td>
                                            </tr>
                                            @if(isset($alert->metadata['deadline']))
                                            <tr>
                                                <td class="detail-label" style="padding: 6px 0; color: #6b7280;">Deadline</td>
                                                <td class="detail-value" style="padding: 6px 0; color: #111827; font-weight: 600;">{{ \Carbon\Carbon::parse($alert->metadata['deadline'])->format('M d, Y') }}</td>
                                            </tr>
                                            @endif
                                            @if(isset($alert->metadata['days_remaining']))
                                            <tr>
                                                <td class="detail-label" style="padding: 6px 0; color: #6b7280;">Days Remaining</td>
                                                <td class="detail-value" style="padding: 6px 0; color: {{ $alert->metadata['days_remaining'] <= 3 ? '#dc2626' : '#111827' }}; font-weight: 600;">{{ $alert->metadata['days_remaining'] }} day(s)</td>
                                            </tr>
                                            @endif
                                            @if(isset($alert->metadata['days_overdue']))
                                            <tr>
                                                <td class="detail-label" style="padding: 6px 0; color: #6b7280;">Days Overdue</td>
                                                <td class="detail-value" style="padding: 6px 0; color: #dc2626; font-weight: 600;">{{ $alert->metadata['days_overdue'] }} day(s)</td>
                                            </tr>
                                            @endif
                                        </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" style="padding-bottom: 28px;">
                                        <a href="{{ $dashboardUrl }}" class="email-btn" style="display: inline-block; background-color: #0a0a0a; color: #ffffff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; letter-spacing: -0.01em;">
                                            View Dashboard &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="email-divider" style="border-bottom: 1px solid #e5e5e5; padding-bottom: 0; line-height: 1px; font-size: 1px;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Footer note -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top: 24px;">
                                        <p class="email-muted" style="margin: 0; font-size: 12px; line-height: 1.6; color: #a3a3a3;">
                                            This is an automated alert from the ZESCO Executive Dashboard. To manage your notification preferences, visit the dashboard settings.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 520px;">
                    <tr>
                        <td align="center" style="padding-top: 24px;">
                            <p class="email-footer-text" style="margin: 0; font-size: 11px; color: #a3a3a3;">
                                &copy; {{ date('Y') }} ZESCO Limited. Authorized access only.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
