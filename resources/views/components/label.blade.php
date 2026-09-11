@props(['required' => false, 'value'])

<label {{ $attributes->merge(['class' => 'mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300']) }}>
    {{ $value ?? $slot }}

    @if ($required)
        <span class="text-red-500">*</span>
    @endif
</label>