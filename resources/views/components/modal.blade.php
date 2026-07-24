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

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-{{ $size }} modal-dialog-centered">
        <div class="modal-content">
            @if($formAction)
                <form action="{{ $formAction }}" method="{{ $formMethod === 'GET' ? 'GET' : 'POST' }}" id="{{ $formId }}" enctype="multipart/form-data">
                    @csrf
                    @if(!in_array($formMethod, ['GET', 'POST']))
                        @method($formMethod)
                    @endif
            @endif

            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                {{ $slot }}
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                @if(!$hideSubmit)
                    <button type="submit" class="btn btn-primary" id="{{ $formId ? $formId . '_submit' : '' }}">{{ $submitText }}</button>
                @endif
            </div>

            @if($formAction)
                </form>
            @endif
        </div>
    </div>
</div>
