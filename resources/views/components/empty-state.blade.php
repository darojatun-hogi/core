@props(['icon' => 'icon-[tabler--inbox]', 'title', 'description' => null])

<div {{ $attributes->class('flex flex-col items-center px-6 py-12 text-center') }}>
	<span class="{{ $icon }} mb-3 size-8 text-base-content/50" aria-hidden="true"></span>
	<p class="font-medium text-base-content">{{ $title }}</p>
	@if ($description)
		<p class="mt-1 max-w-sm text-sm text-base-content/70">{{ $description }}</p>
	@endif
	@if ($slot->isNotEmpty())
		<div class="mt-4">{{ $slot }}</div>
	@endif
</div>
