@props(['tabs' => [], 'active' => null])

<div {{ $attributes->merge(['class' => 'no-scrollbar flex items-center gap-1 overflow-x-auto rounded-2xl bg-gray-100/80 p-1.5 dark:bg-gray-800/60']) }}>
    @foreach ($tabs as $tab)
        @php $isActive = ($active ?? null) === $tab['key']; @endphp
        <a
            href="{{ $tab['url'] }}"
            aria-current="{{ $isActive ? 'page' : 'false' }}"
            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors
                @if ($isActive)
                    bg-white text-brand-600 shadow-sm ring-1 ring-gray-200 dark:bg-brand-500/15 dark:text-brand-400 dark:ring-brand-500/30
                @else
                    text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/60 dark:hover:text-gray-100
                @endif"
        >
            @if (! empty($tab['icon']))
                <x-dynamic-component :component="'heroicon-o-'.$tab['icon']" class="h-4 w-4 shrink-0" />
            @endif
            <span>{{ $tab['label'] }}</span>
            @if (array_key_exists('count', $tab) && $tab['count'] !== null)
                <span class="rounded-full px-1.5 py-0.5 text-[11px] font-semibold
                    @if ($isActive)
                        bg-brand-100 text-brand-700 dark:bg-brand-500/25 dark:text-brand-300
                    @else
                        bg-gray-200/80 text-gray-600 dark:bg-gray-700 dark:text-gray-300
                    @endif"
                >
                    {{ $tab['count'] }}
                </span>
            @endif
        </a>
    @endforeach
</div>