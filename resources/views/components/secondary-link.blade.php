@props(['href', 'label'])

<a class="inline-block align-baseline font-bold text-sm text-blue-500
  hover:text-blue-800 cursor-pointer"
  href="{{ $href }}">
  {{ $label }}
</a>