<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>ZESCO — Weekly Performance Digest</title>
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
            .section-bg { background-color: #1a1a1a !important; border-color: #333 !important; }
            .section-title { color: #ccc !important; }
            .item-text { color: #bbb !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" class="email-body" style="background-color: #fafafa; padding: 48px 16px;">

                <!-- Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 560px;">
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

                            <!-- Badge -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 20px;">
                                        <span style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 4px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">
                                            WEEKLY DIGEST — AI POWERED
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

                            <!-- Headline & Greeting -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top: 28px;">
                                        <h1 class="email-heading" style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #0a0a0a; letter-spacing: -0.025em;">
                                            {{ $digest['headline'] ?? 'Weekly Performance Digest' }}
                                        </h1>
                                        <p class="email-subtext" style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #525252;">
                                            Hello{{ $user->name ? ' ' . explode(' ', $user->name)[0] : '' }}, here's your AI-generated performance summary for the week.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Executive Summary -->
                            @if(!empty($digest['executive_summary']))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="section-bg" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                                            <tr>
                                                <td style="padding: 20px;">
                                                    <p class="email-subtext" style="margin: 0; font-size: 14px; line-height: 1.7; color: #374151;">
                                                        {{ $digest['executive_summary'] }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Top Performers -->
                            @if(!empty($digest['top_performers']))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p class="section-title" style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase; letter-spacing: 0.05em;">
                                            ★ Top Performers
                                        </p>
                                        @foreach($digest['top_performers'] as $performer)
                                        <p class="item-text" style="margin: 0 0 6px 0; font-size: 13px; line-height: 1.5; color: #4b5563;">
                                            <strong>{{ $performer['directorate'] ?? 'N/A' }}</strong> — {{ $performer['highlight'] ?? '' }}
                                        </p>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Areas of Concern -->
                            @if(!empty($digest['areas_of_concern']))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p class="section-title" style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #dc2626; text-transform: uppercase; letter-spacing: 0.05em;">
                                            ⚠ Areas of Concern
                                        </p>
                                        @foreach($digest['areas_of_concern'] as $concern)
                                        <p class="item-text" style="margin: 0 0 6px 0; font-size: 13px; line-height: 1.5; color: #4b5563;">
                                            <strong>{{ $concern['directorate'] ?? 'N/A' }}</strong> — {{ $concern['concern'] ?? '' }}
                                        </p>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- KPI Movements -->
                            @if(!empty($digest['kpi_movements']))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p class="section-title" style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.05em;">
                                            📊 Key KPI Movements
                                        </p>
                                        @foreach($digest['kpi_movements'] as $movement)
                                        <p class="item-text" style="margin: 0 0 6px 0; font-size: 13px; line-height: 1.5; color: #4b5563;">
                                            <strong>{{ $movement['kpi'] ?? 'N/A' }}</strong> — {{ $movement['movement'] ?? '' }}
                                        </p>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Week Ahead Outlook -->
                            @if(!empty($digest['week_ahead_outlook']))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <p class="section-title" style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.05em;">
                                            🔮 Week Ahead
                                        </p>
                                        <p class="item-text" style="margin: 0; font-size: 13px; line-height: 1.5; color: #4b5563; font-style: italic;">
                                            {{ $digest['week_ahead_outlook'] }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" style="padding-bottom: 20px;">
                                        <a href="{{ $dashboardUrl }}" class="email-btn" style="display: inline-block; background-color: #0a0a0a; color: #ffffff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; letter-spacing: -0.01em;">
                                            View Full Dashboard &rarr;
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
                                    <td style="padding-top: 20px;">
                                        <p class="email-muted" style="margin: 0; font-size: 12px; line-height: 1.6; color: #a3a3a3;">
                                            This digest was generated by AI analysis of your ZESCO Executive Dashboard data. For more detailed insights, visit the <a href="{{ $aiInsightsUrl }}" style="color: #6b7280; text-decoration: underline;">AI Insights panel</a>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 560px;">
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
