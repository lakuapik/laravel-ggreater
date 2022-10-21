@props(['label', 'name', 'placeholder', 'type'])

<div class="mt-2">
  <label class="block text-gray-700 text-sm font-bold mb-2" for="{{ $name }}">
    {{ $label }}
  </label>
  <input
    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700
      mb-3 leading-tight focus:outline-none focus:shadow-outline
      @error($name) border-red-500 @enderror"
    id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}"
    type="{{ @$type ?: 'text' }}" placeholder="{{ $placeholder }}">
  @error($name)
    <p class="text-red-500 text-xs italic">{{ $message }}</p>
  @enderror
</div>