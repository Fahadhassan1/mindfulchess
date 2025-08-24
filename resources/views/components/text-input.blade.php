@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-primary-800 focus:ring-primary-800 rounded-md shadow-sm']) !!}>
