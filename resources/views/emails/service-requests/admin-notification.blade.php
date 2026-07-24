@php
    $contact = app(\App\Settings\ContactSettings::class);
    $companyName = $contact->company_name ?: config('app.name');
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Yêu cầu dịch vụ mới</title>
</head>
<body style="background:#f4f5f7;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;box-shadow:0 8px 24px rgba(0,0,0,.05);">
        <tr>
            <td style="padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
                <h2 style="margin:0;color:#1e3a8a;">{{ $companyName }}</h2>
                <p style="margin:4px 0 0;color:#6b7280;">Thông tin yêu cầu dịch vụ mới</p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 0;">
                {!! $htmlBody !!}
            </td>
        </tr>
        <tr>
            <td style="padding-top:20px;font-size:12px;color:#6b7280;">
                <p style="margin:0 0 6px 0;">Từ hệ thống: {{ config('app.name') }}</p>
                <p style="margin:0;">Bạn có thể mở nhanh trong Admin: <a href="{{ $adminViewLink }}">{{ $adminViewLink }}</a></p>
            </td>
        </tr>
    </table>
</body>
</html>

