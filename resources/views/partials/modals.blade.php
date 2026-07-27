<div class="modal fade search-modal" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><h2 class="h4 fw-bold mb-0" id="searchModalLabel">Bạn đang cần sản phẩm nào?</h2></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <form class="position-relative" action="{{ route('catalog') }}" method="get">
                    <input class="form-control" type="search" name="q" placeholder="Nhập tên sản phẩm, thương hiệu..." required>
                    <button class="btn btn-primary btn-icon position-absolute top-50 end-0 translate-middle-y me-2" type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
                </form>
                <div class="mt-4 small text-muted">Gợi ý: serum, sữa rửa mặt, chống nắng, dưỡng thể</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade quick-view-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute end-0 top-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Đóng"></button>
            <div class="row g-0">
                <div class="col-md-6 quick-view-image"><img data-quick-image src="" alt=""></div>
                <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-center">
                    <div class="product-brand mb-2" data-quick-brand></div>
                    <h2 class="h4 fw-bold" id="quickViewTitle" data-quick-title>Sản phẩm</h2>
                    <div class="my-3">
                        <span class="product-price fs-4" data-quick-price></span>
                        <span class="product-old-price" data-quick-old-price></span>
                    </div>
                    <p class="text-muted small">Xem nhanh thông tin và thêm sản phẩm vào giỏ. Chi tiết thành phần, cách dùng và lưu ý có tại trang sản phẩm.</p>
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-primary flex-grow-1" type="button" data-add-cart data-product-id="" data-variant-id="" data-product-name="Sản phẩm"><i class="bi bi-bag-plus me-2"></i>Thêm vào giỏ</button>
                        <a class="btn btn-primary flex-grow-1 d-none" href="{{ route('catalog') }}" data-quick-select-variant><i class="bi bi-sliders me-2"></i>Chọn phân loại</a>
                        <button class="btn btn-outline-primary btn-icon" type="button" data-wishlist="" aria-label="Yêu thích"><i class="bi bi-heart"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<aside class="cart-confirmation" data-cart-confirmation aria-live="polite" aria-hidden="true">
    <button class="cart-confirmation__close" type="button" data-cart-confirmation-close aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
    <div class="cart-confirmation__icon"><i class="bi bi-check2"></i></div>
    <div>
        <strong>Đã thêm vào giỏ</strong>
        <p data-cart-confirmation-name>Sản phẩm</p>
        <div class="cart-confirmation__actions">
            <a href="{{ route('cart') }}">Xem giỏ hàng</a>
            <a href="{{ route('checkout') }}">Thanh toán ngay</a>
        </div>
    </div>
</aside>
