@props(['title', 'description' => null, 'back' => null])

<div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-4">
	<div class="min-w-0 grow basis-72">
		@if ($back)
			<a href="{{ $back }}" class="link link-hover mb-2 inline-flex items-center gap-1 text-sm text-base-content/70">
				<span class="icon-[tabler--chevron-left] size-4"></span>
				Kembali
			</a>
		@endif
		<h1 class="text-2xl font-semibold tracking-tight text-base-content">{{ $title }}</h1>
		@if ($description)
			<p class="mt-1 text-sm text-base-content/70">{{ $description }}</p>
		@endif
	</div>
	@isset($actions)
		<div class="flex flex-wrap items-center gap-2">
			{{ $actions }}
		</div>
	@endisset
</div>
