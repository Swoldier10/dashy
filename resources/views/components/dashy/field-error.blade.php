@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'dashy-error']) }}>{{ $message }}</p>
@enderror
