<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>ZESCO Dashboard - Sign In</title>
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
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Dark mode styles */
        @media (prefers-color-scheme: dark) {
            .email-body {
                background-color: #0a0a0a !important;
            }
            .email-card {
                background-color: #141414 !important;
                border-color: #262626 !important;
            }
            .email-heading {
                color: #ffffff !important;
            }
            .email-subtext {
                color: #a3a3a3 !important;
            }
            .email-muted {
                color: #737373 !important;
            }
            .email-divider {
                border-color: #262626 !important;
            }
            .email-btn {
                background-color: #ffffff !important;
                color: #000000 !important;
            }
            .email-url-text {
                color: #737373 !important;
            }
            .email-url-link {
                color: #a3a3a3 !important;
            }
            .email-footer-text {
                color: #525252 !important;
            }
            .email-logo-light {
                display: none !important;
            }
            .email-logo-dark {
                display: inline-block !important;
                max-width: 120px !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <!-- Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" class="email-body" style="background-color: #fafafa; padding: 48px 16px;">

                <!-- Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 480px;">
                    <tr>
                        <td class="email-card" style="background-color: #ffffff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 48px 40px;">

                            <!-- Logo -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <!-- Light mode logo (black) -->
                                        <img class="email-logo-light" src="{{ url('/images/zesco_black_logo.svg') }}" alt="ZESCO" width="120" style="display: inline-block; max-width: 120px; height: auto;" />
                                        <!-- Dark mode logo (white) — hidden by default -->
                                        <img class="email-logo-dark" src="{{ url('/images/zesco_white_logo.svg') }}" alt="ZESCO" width="120" style="display: none; max-width: 0; height: 0; overflow: hidden;" />
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="email-divider" style="border-bottom: 1px solid #e5e5e5; padding-bottom: 0; line-height: 1px; font-size: 1px;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top: 32px;">
                                        <h1 class="email-heading" style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #0a0a0a; letter-spacing: -0.025em;">
                                            Sign in to your dashboard
                                        </h1>
                                        <p class="email-subtext" style="margin: 0 0 28px 0; font-size: 14px; line-height: 1.6; color: #525252;">
                                            Hello{{ isset($user) && $user->name ? ' ' . explode(' ', $user->name)[0] : '' }}, click the button below to securely sign in to the ZESCO Executive Dashboard. No password needed.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" style="padding-bottom: 28px;">
                                        <a href="{{ $url }}" class="email-btn" style="display: inline-block; background-color: #0a0a0a; color: #ffffff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; letter-spacing: -0.01em; mso-padding-alt: 14px 32px;">
                                            Sign in to Dashboard &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fallback URL -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 28px;">
                                        <p class="email-url-text" style="margin: 0 0 4px 0; font-size: 12px; color: #a3a3a3;">
                                            If the button doesn&rsquo;t work, copy and paste this link:
                                        </p>
                                        <p style="margin: 0; font-size: 12px; word-break: break-all;">
                                            <a href="{{ $url }}" class="email-url-link" style="color: #525252; text-decoration: underline;">{{ $url }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="email-divider" style="border-bottom: 1px solid #e5e5e5; padding-bottom: 0; line-height: 1px; font-size: 1px;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Security notice -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top: 24px;">
                                        <p class="email-muted" style="margin: 0; font-size: 12px; line-height: 1.6; color: #a3a3a3;">
                                            This link expires in 15 minutes and can only be used once. If you didn&rsquo;t request this email, you can safely ignore it &mdash; your account is secure.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 480px;">
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
