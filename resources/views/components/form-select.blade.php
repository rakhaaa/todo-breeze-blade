@props(['id', 'label', 'name', 'options', 'value' => '', 'required' => false])

<div class="mb-4">
    <label for="{{ $id }}" class="block theme">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $id }}" @if ($required) required @endif
        class="w-full theme p-2 border border-gray-300 rounded mt-2">
        @foreach ($options as $option)
            <option value="{{ $option[1] }}" @if ($value == $option[0]) selected @endif>
                {{ ucfirst($option[1]) }}
            </option>
        @endforeach
    </select>
</div>
