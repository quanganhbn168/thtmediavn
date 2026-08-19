<footer class="site-footer" @if($siteAssets?->getFirstMediaUrl('footer_background')) style="--footer-background-image: url('{{ $siteAssets->getFirstMediaUrl('footer_background') }}')" @endif>
    <div class="site-footer-main">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 xl:gap-12 md:grid-cols-2 lg:grid-cols-12">
                <div class="md:col-span-2 lg:col-span-4">
                    @if($siteAssets?->getFirstMediaUrl('logo_footer') ?: $siteAssets?->getFirstMediaUrl('logo'))
                        <img class="footer-logo mb-3" src="{{ $siteAssets->getFirstMediaUrl('logo_footer') ?: $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
                    @else
                        <div class="mb-4 text-2xl font-extrabold text-white">THT MEDIA VN</div>
                    @endif
                    <div class="footer-text font-bold text-white mb-2">{{ $website['company'] }}</div>
                    <p class="footer-text mb-3">Sản xuất hình ảnh, truyền thông, sự kiện và giải pháp thương hiệu theo nhu cầu thực tế của doanh nghiệp.</p>
                    @if($website['address'])<div class="footer-contact-item"><i class="fa-solid fa-location-dot"></i><span><strong>Trụ sở chính:</strong> {{ $website['address'] }}</span></div>@endif
                    @foreach($website['branches'] ?? [] as $footerBranch)
                        <div class="footer-contact-item"><i class="fa-solid fa-building"></i><span><strong>{{ $footerBranch['name'] }}</strong>: {{ $footerBranch['address'] }}</span></div>
                    @endforeach
                    @php($footerPhones = $website['phones'] ?? [])
                    @if(empty($footerPhones) && $website['phone'])
                        @php($footerPhones = [['label' => 'Điện thoại', 'number' => $website['phone']]])
                    @endif
                    @foreach($footerPhones as $footerPhone)
                        @if(filled($footerPhone['number'] ?? null))<div class="footer-contact-item"><i class="fa-solid fa-phone"></i><span>{{ $footerPhone['label'] ?? 'Điện thoại' }}: </span><a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $footerPhone['number']) }}">{{ $footerPhone['number'] }}</a></div>@endif
                    @endforeach
                    @if($website['email'])<div class="footer-contact-item"><i class="fa-solid fa-envelope"></i><a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a></div>@endif
                </div>

                @if($footerMenus->isNotEmpty())
                    @foreach($footerMenus as $footerMenu)
                        <div class="md:col-span-1 lg:col-span-2">
                            <h2 class="footer-heading">{{ $footerMenu->getTranslation('name', 'vi') }}</h2>
                            @include('partials.menu.footer-items', ['items' => $footerMenu->items])
                        </div>
                    @endforeach
                @else
                    <div class="md:col-span-1 lg:col-span-2">
                        <h2 class="footer-heading">THTMedia</h2>
                        <a class="footer-link" href="{{ route('about') }}">Giới thiệu</a>
                        <a class="footer-link" href="{{ route('projects.index') }}">Dự án đã thực hiện</a>
                        <a class="footer-link" href="{{ route('clients.index') }}">Khách hàng & đối tác</a>
                        <a class="footer-link" href="{{ route('news.index') }}">Tin tức</a>
                        <a class="footer-link" href="{{ route('contact') }}">Liên hệ</a>
                    </div>
                    <div class="md:col-span-1 lg:col-span-2">
                        <h2 class="footer-heading">Dịch vụ</h2>
                        <a class="footer-link" href="{{ route('services.index') }}">Tất cả dịch vụ</a>
                        <a class="footer-link" href="{{ route('services.index', ['group' => 'production']) }}">Sản xuất hình ảnh</a>
                        <a class="footer-link" href="{{ route('services.index', ['group' => 'marketing']) }}">Truyền thông & marketing</a>
                        <a class="footer-link" href="{{ route('policies.privacy') }}">Chính sách bảo mật</a>
                    </div>
                @endif

                <div class="md:col-span-2 lg:col-span-4">
                    <h2 class="footer-heading">Nhận thông tin mới</h2>
                    <p class="footer-text">Đăng ký để nhận tin tức, dự án mới và góc nhìn từ THT Media.</p>
                    @if(session('newsletter_success'))<p class="text-sm text-white"><i class="fa-solid fa-circle-check mr-1"></i>{{ session('newsletter_success') }}</p>@endif
                    <form class="newsletter-form mt-3" action="{{ route('newsletter.store') }}" method="post">@csrf
                        <input class="hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                        <label class="sr-only" for="footer-phone">Số điện thoại</label>
                        <input class="ui-input @error('phone') border-red-500 @enderror" id="footer-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" value="{{ old('phone') }}" placeholder="Số điện thoại" required>
                        <button class="absolute right-1 top-1 inline-flex h-[39px] min-h-0 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-[17px] text-xs font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="submit">Đăng ký</button>
                    </form>
                    @error('phone')<div class="text-sm text-amber-300 mt-2">{{ $message }}</div>@enderror
                    <div class="footer-social-inline">
                        <span class="footer-social-label">Theo dõi và kết nối với THT Media</span>
                        @php($footerSocials = [
                            'facebook' => ['label' => 'Facebook THT Media', 'icon' => 'fa-facebook-f'],
                            'instagram' => ['label' => 'Instagram THT Media', 'icon' => 'fa-instagram'],
                            'youtube' => ['label' => 'YouTube THT Media', 'icon' => 'fa-youtube'],
                            'tiktok' => ['label' => 'TikTok THT Media', 'icon' => 'fa-tiktok'],
                        ])
                        <div class="social-links mt-2" aria-label="Mạng xã hội THT Media">
                            @if($website['social']['zalo'] ?? null)<a class="social-link social-link-zalo" href="{{ $website['social']['zalo'] }}" target="_blank" rel="noopener" aria-label="Zalo THT Media"><img class="zalo-icon" src="{{ asset('assets/images/zalo.svg') }}" alt=""></a>@endif
                            @foreach($footerSocials as $network => $social)
                                @if($website['social'][$network] ?? null)<a class="social-link" href="{{ $website['social'][$network] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}"><i class="fa-brands {{ $social['icon'] }}" aria-hidden="true"></i></a>@endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer-bottom">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-between gap-2 md:flex-row">
            <span>{{ $website['copyright'] ?: '© '.date('Y').' '.$website['company'].'.' }}</span>
            @if($website['business_license'])<span>Mã số thuế: {{ $website['business_license'] }}</span>@endif
        </div>
    </div>
</footer>
