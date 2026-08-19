<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu tư vấn mới</title>
</head>
<body style="margin:0;background:#f5f8f4;color:#1f2b25;font-family:Arial,sans-serif;line-height:1.6;">
    <div style="max-width:680px;margin:32px auto;padding:28px;background:#fff;border:1px solid #e4ece2;border-radius:16px;">
        <p style="margin:0 0 8px;color:#247138;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;">THT Media</p>
        <h1 style="margin:0 0 24px;font-size:24px;line-height:1.25;">Có yêu cầu tư vấn mới</h1>

        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr><td style="padding:9px 0;color:#68756c;width:150px;">Họ và tên</td><td style="padding:9px 0;font-weight:700;">{{ $contact->name }}</td></tr>
            <tr><td style="padding:9px 0;color:#68756c;">Số điện thoại</td><td style="padding:9px 0;"><a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $contact->phone) }}" style="color:#247138;font-weight:700;">{{ $contact->phone }}</a></td></tr>
            @if(filled($contact->email))<tr><td style="padding:9px 0;color:#68756c;">Email</td><td style="padding:9px 0;"><a href="mailto:{{ $contact->email }}" style="color:#247138;">{{ $contact->email }}</a></td></tr>@endif
            @if(filled($contact->company))<tr><td style="padding:9px 0;color:#68756c;">Doanh nghiệp</td><td style="padding:9px 0;">{{ $contact->company }}</td></tr>@endif
            @if($contact->service)<tr><td style="padding:9px 0;color:#68756c;">Dịch vụ</td><td style="padding:9px 0;">{{ $contact->service->getTranslation('name', 'vi') }}</td></tr>@endif
            @if(filled($contact->budget))<tr><td style="padding:9px 0;color:#68756c;">Ngân sách</td><td style="padding:9px 0;">{{ $contact->budget }}</td></tr>@endif
            @if(filled($contact->timeline))<tr><td style="padding:9px 0;color:#68756c;">Thời gian</td><td style="padding:9px 0;">{{ $contact->timeline }}</td></tr>@endif
        </table>

        <div style="margin-top:22px;padding:18px;background:#f7faf5;border-radius:12px;">
            <p style="margin:0 0 8px;color:#68756c;font-size:13px;font-weight:700;">Nội dung cần tư vấn</p>
            <div style="white-space:pre-line;">{{ $contact->message }}</div>
        </div>

        <p style="margin:24px 0 0;color:#68756c;font-size:12px;">Gửi lúc {{ optional($contact->created_at)->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
