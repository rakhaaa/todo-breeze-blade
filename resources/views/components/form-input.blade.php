@props(['id', 'label', 'name', 'type' => 'text', 'value' => '', 'required' => false])

   <div class="mb-4">
       <label for="{{ $id }}" class="block theme">{{ $label }}</label>
       <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" :value="{{ $value }}" @if($required) required @endif class="w-full theme p-2 border border-gray-300 rounded mt-2">
   </div>