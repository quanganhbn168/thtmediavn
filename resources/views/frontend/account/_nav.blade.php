<div class="content-card p-3">
    <div class="fw-bold fs-5 mb-3">Tài khoản của tôi</div>
    <nav class="nav flex-column account-nav">
        <a class="nav-link {{ request()->routeIs('account.index') ? 'active' : '' }}" href="{{ route('account.index') }}">
            <i class="bi bi-grid me-2"></i>Tổng quan
        </a>
        <a class="nav-link {{ request()->routeIs('account.orders*') ? 'active' : '' }}" href="{{ route('account.orders') }}">
            <i class="bi bi-receipt me-2"></i>Đơn hàng
        </a>
        <a class="nav-link {{ request()->routeIs('account.profile') ? 'active' : '' }}" href="{{ route('account.profile') }}">
            <i class="bi bi-person me-2"></i>Thông tin & địa chỉ
        </a>
        <a class="nav-link" href="{{ route('wishlist') }}">
            <i class="bi bi-heart me-2"></i>Sản phẩm yêu thích
        </a>
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button class="nav-link text-danger border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
            </button>
        </form>
    </nav>
</div>
