@php($selectedServiceId = old('service_id', $selectedServiceId ?? null))
<form class="consultation-form grid gap-4 md:grid-cols-2" action="{{ route('contact.submit') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input class="hidden" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div><label class="ui-label" for="consult-name-{{ $formId ?? 'default' }}">Họ và tên</label><input class="ui-input" id="consult-name-{{ $formId ?? 'default' }}" name="name" value="{{ old('name') }}" autocomplete="name" required></div>
    <div><label class="ui-label" for="consult-phone-{{ $formId ?? 'default' }}">Số điện thoại</label><input class="ui-input" id="consult-phone-{{ $formId ?? 'default' }}" name="phone" value="{{ old('phone') }}" type="tel" inputmode="tel" autocomplete="tel" required></div>
    @if(($extended ?? false))
        <div><label class="ui-label" for="consult-email-{{ $formId ?? 'default' }}">Email</label><input class="ui-input" id="consult-email-{{ $formId ?? 'default' }}" name="email" value="{{ old('email') }}" type="email" autocomplete="email"></div>
        <div><label class="ui-label" for="consult-company-{{ $formId ?? 'default' }}">Doanh nghiệp</label><input class="ui-input" id="consult-company-{{ $formId ?? 'default' }}" name="company" value="{{ old('company') }}" autocomplete="organization"></div>
    @endif
    <div><label class="ui-label" for="consult-service-{{ $formId ?? 'default' }}">Dịch vụ quan tâm</label><select class="ui-select" id="consult-service-{{ $formId ?? 'default' }}" name="service_id"><option value="">Cần THT Media tư vấn</option>@foreach($consultingServices ?? [] as $id => $label)<option value="{{ $id }}" @selected((string) $selectedServiceId === (string) $id)>{{ $label }}</option>@endforeach</select></div>
    @if(($extended ?? false))
        <div><label class="ui-label" for="consult-budget-{{ $formId ?? 'default' }}">Ngân sách dự kiến</label><select class="ui-select" id="consult-budget-{{ $formId ?? 'default' }}" name="budget"><option value="">Chưa xác định</option>@foreach(['Dưới 30 triệu', '30–80 triệu', '80–200 triệu', 'Trên 200 triệu'] as $budget)<option @selected(old('budget') === $budget)>{{ $budget }}</option>@endforeach</select></div>
        <div><label class="ui-label" for="consult-timeline-{{ $formId ?? 'default' }}">Thời gian dự kiến</label><input class="ui-input" id="consult-timeline-{{ $formId ?? 'default' }}" name="timeline" value="{{ old('timeline') }}" placeholder="Ví dụ: Tháng 10/2026"></div>
        <div><label class="ui-label" for="consult-attachment-{{ $formId ?? 'default' }}">Tài liệu brief</label><input class="ui-input" id="consult-attachment-{{ $formId ?? 'default' }}" name="attachment" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"></div>
    @endif
    <div class="md:col-span-2"><label class="ui-label" for="consult-message-{{ $formId ?? 'default' }}">Nội dung cần tư vấn</label><textarea class="ui-input" id="consult-message-{{ $formId ?? 'default' }}" name="message" rows="4" required placeholder="Mục tiêu, phạm vi, địa điểm hoặc thời gian dự kiến...">{{ old('message') }}</textarea></div>
    <div class="md:col-span-2"><button class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-6 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="submit"><i class="fa-solid fa-paper-plane mr-2"></i>Nhận tư vấn cho dự án</button></div>
</form>
