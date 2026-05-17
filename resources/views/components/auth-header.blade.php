@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <x-dashy.heading size="xl">{{ $title }}</x-dashy.heading>
    <x-dashy.subheading>{{ $description }}</x-dashy.subheading>
</div>
