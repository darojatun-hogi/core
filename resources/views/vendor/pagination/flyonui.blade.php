@if ($paginator->hasPages())
	<nav role="navigation" aria-label="Navigasi halaman"
		class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
		<p class="text-sm text-base-content/70">
			{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
		</p>

		<ul class="flex flex-wrap items-center justify-center gap-1">
			<li>
				@if ($paginator->onFirstPage())
					<span class="btn btn-text btn-square btn-sm btn-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
						<span class="icon-[tabler--chevron-left] size-5"></span>
					</span>
				@else
					<a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-text btn-square btn-sm"
						aria-label="Halaman sebelumnya">
						<span class="icon-[tabler--chevron-left] size-5"></span>
					</a>
				@endif
			</li>

			@foreach ($elements as $element)
				@if (is_string($element))
					<li><span class="px-2 text-sm text-base-content/70">{{ $element }}</span></li>
				@endif

				@if (is_array($element))
					@foreach ($element as $page => $url)
						<li>
							@if ($page == $paginator->currentPage())
								<span aria-current="page" class="btn btn-primary btn-square btn-sm">{{ $page }}</span>
							@else
								<a href="{{ $url }}" class="btn btn-text btn-square btn-sm" aria-label="Halaman {{ $page }}">{{ $page }}</a>
							@endif
						</li>
					@endforeach
				@endif
			@endforeach

			<li>
				@if ($paginator->hasMorePages())
					<a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-text btn-square btn-sm"
						aria-label="Halaman berikutnya">
						<span class="icon-[tabler--chevron-right] size-5"></span>
					</a>
				@else
					<span class="btn btn-text btn-square btn-sm btn-disabled" aria-disabled="true" aria-label="Halaman berikutnya">
						<span class="icon-[tabler--chevron-right] size-5"></span>
					</span>
				@endif
			</li>
		</ul>
	</nav>
@endif
