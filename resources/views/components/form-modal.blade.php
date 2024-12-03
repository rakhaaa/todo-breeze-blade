<div x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
    <div x-on:click.away="open = false" class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">{{ $title }}</h3>
            <button x-on:click="open = false" class="text-gray-500">&times;</button>
        </div>
        {{ $slot }}
    </div>
</div>