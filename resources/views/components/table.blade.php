<table class="min-w-full">
    <thead class="theme">
        <tr>
            @foreach ($headers as $header)
                <th class="py-2 px-4">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>