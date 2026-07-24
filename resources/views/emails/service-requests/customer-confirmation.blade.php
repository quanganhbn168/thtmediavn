@php
    $contact = app(\App\Settings\ContactSettings::class);
    $companyName = $contact->company_name ?: config('app.name');
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Xác nhận yêu cầu</title>
</head>
<body style="background:#f4f5f7;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;box-shadow:0 8px 24px rgba(0,0,0,.05);">
        <tr>
            <td style="padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
                <h2 style="margin:0;color:#065f46;">{{ $companyName }}</h2>
                <p style="margin:4px 0 0;color:#6b7280;">Xác nhận đã nhận yêu cầu cho {{ $serviceName }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 0;">
                {!! $htmlBody !!}
            </td>
        </tr>
        <tr>
            <td style="padding-top:20px;font-size:12px;color:#6b7280;">
                <p style="margin:0;">Vui lòng giữ lại email này để tham khảo trong quá trình xử lý.</p>
            </td>
        </tr>
    </table>
</body>
</html>

