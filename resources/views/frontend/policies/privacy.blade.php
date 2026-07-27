@extends('layouts.master')

@section('title', 'Chính sách bảo mật — '.$website['name'])

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active">Chính sách bảo mật</li></ol></nav></div></div>
<section class="section-space bg-soft">
    <div class="container"><article class="content-card policy-content mx-auto">
        <h1 class="h2">Chính sách bảo mật</h1>
        <p>RHEA thu thập thông tin liên hệ và giao nhận mà khách hàng cung cấp để xử lý đơn hàng, hỗ trợ sau bán và thực hiện nghĩa vụ vận hành liên quan.</p>
        <h2 class="h4">Thông tin được sử dụng</h2>
        <p>Thông tin có thể gồm họ tên, số điện thoại, email, địa chỉ giao hàng, nội dung đơn và yêu cầu xuất hóa đơn.</p>
        <h2 class="h4">Bảo vệ và chia sẻ dữ liệu</h2>
        <p>Thông tin chỉ được sử dụng trong phạm vi cần thiết để phục vụ khách hàng và có thể được chuyển cho đơn vị giao nhận hoặc bên cung cấp dịch vụ liên quan đến đơn hàng.</p>
        <h2 class="h4">Yêu cầu cập nhật thông tin</h2>
        <p class="mb-0">Khách hàng có thể liên hệ qua <a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a> hoặc hotline {{ $website['phone'] }} để yêu cầu hỗ trợ.</p>
    </article></div>
</section>
@endsection
