<div
    x-data="{ query: '' }"
    class="space-y-4 xl:max-h-[calc(100vh-10rem)] xl:overflow-y-auto xl:overscroll-contain xl:pr-2"
>
    <div class="relative">
        <input
            type="search"
            x-model="query"
            wire:model.live.debounce.350ms="menuSourceSearch"
            placeholder="Tìm nội dung..."
            class="fi-input block w-full rounded-lg border-none bg-gray-50 px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 outline-none placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
        >
    </div>

    <p class="text-xs text-gray-500">Tối đa 50 mục mỗi nhóm. Nhập từ khóa để tìm trong toàn bộ nội dung.</p>
    <div class="space-y-2">
        @foreach ($sourceGroups as $group)
            <details x-bind:open="query.length > 0 || {{ $loop->first ? 'true' : 'false' }}" class="group rounded-xl border border-gray-950/10 bg-white dark:border-white/10 dark:bg-white/[0.02]" @if ($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-semibold text-gray-950 marker:hidden dark:text-white">
                    <span>{{ $group['label'] }}</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ count($group['items']) }}</span>
                </summary>

                <div class="space-y-1 border-t border-gray-950/10 p-2 dark:border-white/10">
                    @foreach ($group['items'] as $item)
                        <button
                            type="button"
                            x-show="! query || @js(mb_strtolower($item['label'].' '.$item['meta'])).includes(query.toLowerCase())"
                            x-cloak
                            wire:click="addMenuItemFromSource('{{ $item['key'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="addMenuItemFromSource('{{ $item['key'] }}')"
                            aria-label="Thêm {{ $item['label'] }} vào menu"
                            title="Thêm vào menu"
                            class="flex w-full items-start justify-between gap-3 rounded-lg px-2.5 py-2.5 text-left transition hover:bg-primary-50 hover:text-primary-700 disabled:cursor-wait disabled:opacity-60 dark:hover:bg-primary-400/10 dark:hover:text-primary-300"
                        >
                            <span class="min-w-0 break-words">
                                <span class="block whitespace-normal break-words text-sm font-medium leading-5" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                            </span>
                            <span class="shrink-0 pt-0.5 text-lg leading-none text-primary-600 dark:text-primary-400" aria-hidden="true">+</span>
                        </button>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>

    <div class="rounded-xl border border-dashed border-gray-950/15 p-3 dark:border-white/15">
        <p class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">Liên kết tuỳ chỉnh</p>

        <div class="space-y-2">
            <input
                type="text"
                wire:model.live.debounce.250ms="customMenuLabel"
                placeholder="Nhãn hiển thị"
                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 outline-none placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
            >
            <input
                type="url"
                wire:model.live.debounce.250ms="customMenuUrl"
                placeholder="https://example.com/..."
                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 outline-none placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
            >
            <button
                type="button"
                wire:click="addCustomMenuItem"
                class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                wire:loading.attr="disabled"
                wire:target="addCustomMenuItem"
            >
                Thêm liên kết
            </button>
        </div>
    </div>

    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
        Mục được thêm vào cuối danh sách. Mở từng mục ở bên phải để chỉnh nhãn và cách mở liên kết rồi lưu một lần.
    </p>
</div>
