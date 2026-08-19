<div class="floating-actions" aria-label="Liên hệ nhanh">
    @if($website['social']['facebook'] ?? null)
        <a class="floating-action facebook" href="{{ $website['social']['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook THT Media"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i><span class="floating-tooltip">Facebook THT Media</span></a>
    @endif
    @if($website['social']['zalo'] ?? null)
        <a class="floating-action zalo" href="{{ $website['social']['zalo'] }}" target="_blank" rel="noopener" aria-label="Liên hệ Zalo"><img class="zalo-icon" src="{{ asset('assets/images/zalo.svg') }}" alt=""><span class="floating-tooltip">Liên hệ Zalo</span></a>
    @endif
    @if($website['phone'])
        <a class="floating-action phone" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $website['phone']) }}" aria-label="Gọi THTMedia"><i class="fa-solid fa-phone" aria-hidden="true"></i><span class="floating-tooltip">Gọi THTMedia</span></a>
    @endif
</div>

<button class="floating-action back-to-top" type="button" data-back-to-top aria-label="Lên đầu trang">
    <span class="back-to-top-progress" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><circle class="back-to-top-progress__track" cx="24" cy="24" r="20"></circle><circle class="back-to-top-progress__value" cx="24" cy="24" r="20" transform="rotate(-90 24 24)" data-back-to-top-progress></circle></svg></span>
    <i class="fa-solid fa-angle-up" aria-hidden="true"></i><span class="floating-tooltip">Lên đầu trang</span>
</button>
