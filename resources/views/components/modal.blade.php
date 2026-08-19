@props([
    'id',
    'title' => '',
    'size' => 'md',
    'submitText' => 'Lưu lại',
    'formAction' => null,
    'formMethod' => 'POST',
    'formId' => null,
    'hideSubmit' => false
])

<div class="ui-modal hidden" id="{{ $id }}" data-ui-modal tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="ui-modal__backdrop" data-ui-modal-close></div>
    <div class="ui-modal__dialog ui-modal__dialog--{{ $size }}" role="dialog" aria-modal="true">
        <div class="ui-modal__content">
            @if($formAction)
                <form action="{{ $formAction }}" method="{{ $formMethod === 'GET' ? 'GET' : 'POST' }}" id="{{ $formId }}" enctype="multipart/form-data">
                    @csrf
                    @if(!in_array($formMethod, ['GET', 'POST']))
                        @method($formMethod)
                    @endif
            @endif

            <div class="ui-modal__header">
                <h2 class="ui-modal__title" id="{{ $id }}Label">{{ $title }}</h2>
                <button type="button" class="ui-modal__close" data-ui-modal-close aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="ui-modal__body">
                {{ $slot }}
            </div>

            <div class="ui-modal__footer">
                <button type="button" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-secondary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-orange-700 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary" data-ui-modal-close>Đóng</button>
                @if(!$hideSubmit)
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" id="{{ $formId ? $formId . '_submit' : '' }}">{{ $submitText }}</button>
                @endif
            </div>

            @if($formAction)
                </form>
            @endif
        </div>
    </div>
</div>
