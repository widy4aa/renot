<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            background-color: #F0F0EE;
            color: #1a1a2e;
            -webkit-font-smoothing: antialiased;
        }

        .outer {
            padding: 40px 16px;
        }

        .wrapper {
            max-width: 620px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        /* ── Header ── */
        .header {
            background-color: #ED1B2F;
            padding: 32px 40px 28px;
        }

        .header-brand {
            display: flex;
            align-items: baseline;
            gap: 2px;
            margin-bottom: 4px;
        }

        .header-re {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -1px;
            line-height: 1;
        }

        .header-not {
            font-size: 32px;
            font-weight: 300;
            color: rgba(255,255,255,0.75);
            letter-spacing: -1px;
            line-height: 1;
        }

        .header-tagline {
            font-size: 13px;
            color: rgba(255,255,255,0.65);
            letter-spacing: 0.3px;
            margin-top: 2px;
        }

        /* ── Banner accent strip ── */
        .accent-strip {
            height: 4px;
            background: linear-gradient(90deg, #ED1B2F 0%, #006CB8 50%, #ACC42A 100%);
        }

        /* ── Body content ── */
        .content {
            padding: 44px 40px 36px;
        }

        /* Salam pembuka */
        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        /* Body teks utama */
        .email-body {
            font-size: 16px;
            line-height: 1.85;
            color: #374151;
            white-space: pre-line;
        }

        /* ── Info box / card detail ── */
        .info-box {
            margin: 28px 0;
            background-color: #F8F8F6;
            border-left: 4px solid #ED1B2F;
            border-radius: 0 10px 10px 0;
            padding: 20px 24px;
        }

        .info-box-title {
            font-size: 11px;
            font-weight: 700;
            color: #ED1B2F;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 15px;
            line-height: 1.5;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: #6B7280;
            min-width: 160px;
            flex-shrink: 0;
        }

        .info-value {
            color: #111827;
            font-weight: 600;
        }

        /* ── CTA Button ── */
        .cta-wrap {
            margin: 32px 0 4px;
            text-align: center;
        }

        .cta-btn {
            display: inline-block;
            background-color: #ED1B2F;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            letter-spacing: 0.3px;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1.5px solid #E5E7EB;
            margin: 32px 0;
        }

        /* ── Footer ── */
        .footer {
            background-color: #F8F8F6;
            border-top: 1.5px solid #E5E7EB;
            padding: 24px 40px;
            text-align: center;
        }

        .footer-logo {
            font-size: 15px;
            font-weight: 700;
            color: #ED1B2F;
            margin-bottom: 6px;
        }

        .footer p {
            font-size: 12px;
            color: #9CA3AF;
            line-height: 1.7;
        }

        .footer strong {
            color: #6B7280;
        }
    </style>
</head>
<body>
    <div class="outer">
        <div class="wrapper">

            {{-- Header --}}
            <div class="header">
                <div class="header-brand">
                    <span class="header-re">Re</span><span class="header-not">Not</span>
                </div>
                <div class="header-tagline">Reminder &amp; Notifikasi Sertifikasi Pegawai &mdash; Pertamina</div>
            </div>

            {{-- Accent strip warna --}}
            <div class="accent-strip"></div>

            {{-- Konten email dari template database --}}
            <div class="content">
                <div class="email-body">{{ $body }}</div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <div class="footer-logo">ReNot</div>
                <p>
                    Email ini dikirim otomatis oleh sistem <strong>ReNot</strong>.<br>
                    Mohon tidak membalas email ini. Untuk pertanyaan, hubungi administrator.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
