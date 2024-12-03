@props(['color' => 'green'])

<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="w-fit bg-{{ $color }}-500 text-white p-2 mb-4 rounded">
    {{ $slot }}
</div>