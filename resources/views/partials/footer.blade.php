<footer class="site-footer">
    <div class="site-footer-main">
        <div class="container">
            <div class="row g-4 g-xl-5">
                <div class="col-lg-4 col-md-6">
                    @if($siteAssets?->getFirstMediaUrl('logo_footer') ?: $siteAssets?->getFirstMediaUrl('logo'))
                        <img class="footer-logo mb-3" src="{{ $siteAssets->getFirstMediaUrl('logo_footer') ?: $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
                    @else
                        <div class="h3 fw-black text-white mb-3">RHEA SKINLAB</div>
                    @endif
                    <div class="footer-text fw-bold text-white mb-2">{{ $website['company'] }}</div>
                    <p class="footer-text mb-2">Mỹ phẩm chính hãng, lựa chọn theo nhu cầu và đặc điểm làn da.</p>
                    <div class="footer-contact-item"><i class="bi bi-geo-alt-fill"></i><span>{{ $website['address'] }}</span></div>
                    <div class="footer-contact-item"><i class="bi bi-telephone-fill"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $website['phone']) }}">{{ $website['phone'] }}</a></div>
                    <div class="footer-contact-item"><i class="bi bi-envelope-fill"></i><a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a></div>
                </div>
                @if($footerMenus->isNotEmpty())
                    @foreach($footerMenus as $footerMenu)
                        <div class="col-lg-2 col-md-6 col-6">
                            <h2 class="footer-heading">{{ $footerMenu->getTranslation('name', 'vi') }}</h2>
                            @include('partials.menu.footer-items', ['items' => $footerMenu->items])
                        </div>
                    @endforeach
                @else
                    <div class="col-lg-2 col-md-6 col-6">
                        <h2 class="footer-heading">Chính sách</h2>
                        <a class="footer-link" href="{{ route('about') }}">Về chúng tôi</a>
                        <a class="footer-link" href="{{ route('contact') }}">Thông tin liên hệ</a>
                    </div>
                    <div class="col-lg-2 col-md-6 col-6">
                        <h2 class="footer-heading">Hỗ trợ</h2>
                        <a class="footer-link" href="{{ route('contact') }}">Liên hệ</a>
                        <a class="footer-link" href="{{ route('catalog') }}">Tìm kiếm sản phẩm</a>
                        <a class="footer-link" href="{{ route('cart') }}">Giỏ hàng</a>
                        <a class="footer-link" href="{{ route('login') }}">Tài khoản</a>
                        <a class="footer-link" href="{{ route('news.index') }}">Kiến thức làm đẹp</a>
                    </div>
                @endif
                <div class="col-lg-4 col-md-6">
                    <h2 class="footer-heading">Nhận ưu đãi mới</h2>
                    <p class="footer-text">Đăng ký email để nhận thông tin sản phẩm mới và chương trình khuyến mãi.</p>
                    <form class="newsletter-form mt-3" action="{{ route('newsletter.store') }}" method="post">@csrf
                        <label class="visually-hidden" for="footer-email">Email</label>
                        <input class="form-control" id="footer-email" name="email" type="email" placeholder="Nhập email của bạn" required>
                        <button class="btn btn-primary" type="submit">Đăng ký</button>
                    </form>
                    <div class="mt-4">
                        <div class="footer-heading mb-2">Liên kết xã hội</div>
                        <div class="social-links">
                            @foreach(['facebook' => 'facebook', 'instagram' => 'instagram', 'youtube' => 'youtube', 'tiktok' => 'tiktok'] as $network => $icon)
                                @if($website['social'][$network] ?? null)
                                    <a class="social-link" href="{{ $website['social'][$network] }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}"><i class="bi bi-{{ $icon }}"></i></a>
                                @endif
                            @endforeach
                            @if($website['social']['zalo'] ?? null)
                                <a class="social-link zalo-link" href="{{ $website['social']['zalo'] }}" target="_blank" rel="noopener" aria-label="Zalo">
                                    <img class="zalo-icon" src="{{ asset('assets/images/zalo.svg') }}" alt="">
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-2" aria-label="Phương thức thanh toán">
                        <span class="payment-badge">COD</span>
                        <span class="payment-badge">Chuyển khoản</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
            <span>{{ $website['copyright'] ?: '© '.date('Y').' '.$website['company'].'.' }}</span>
            @if($website['business_license'])
                <span>Giấy chứng nhận đăng ký kinh doanh số {{ $website['business_license'] }}</span>
            @endif
        </div>
    </div>
</footer>
