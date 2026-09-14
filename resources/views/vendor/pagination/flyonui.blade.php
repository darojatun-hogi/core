@if ($paginator->hasPages())
	<div class="mb-3 flex flex-col gap-1 text-sm text-base-content/70 justify-center max-xl:hidden">
		<span>
			Showing {{ $paginator->firstItem() ?: 0 }} - {{ $paginator->lastItem() ?: 0 }} of {{ $paginator->total() }} data
		</span>
	</div>

	<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
		<ul class="join flex-wrap justify-center gap-1">
			{{-- Previous Page Link --}}
			@if ($paginator->onFirstPage())
				<li>
					<span class="join-item btn btn-soft btn-disabled btn-xs sm:btn-md" aria-disabled="true" aria-label="Previous page">
						<span aria-hidden="true">«</span>
					</span>
				</li>
			@else
				<li>
					<a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="join-item btn btn-soft btn-xs sm:btn-md"
						aria-label="Previous page">
						<span aria-hidden="true">«</span>
					</a>
				</li>
			@endif

			{{-- Pagination Elements --}}
			@foreach ($elements as $element)
				@if (is_string($element))
					<li>
						<span class="join-item btn btn-soft btn-disabled btn-xs sm:btn-md">{{ $element }}</span>
					</li>
				@endif

				@if (is_array($element))
					@foreach ($element as $page => $url)
						@if ($page == $paginator->currentPage())
							<li>
								<span aria-current="page" class="join-item btn btn-primary btn-xs sm:btn-md"
									aria-label="Page {{ $page }}">
									{{ $page }}
								</span>
							</li>
						@else
							<li>
								<a href="{{ $url }}" class="join-item btn btn-soft btn-xs sm:btn-md"
									aria-label="Go to page {{ $page }}">
									{{ $page }}
								</a>
							</li>
						@endif
					@endforeach
				@endif
			@endforeach

			{{-- Next Page Link --}}
			@if ($paginator->hasMorePages())
				<li>
					<a href="{{ $paginator->nextPageUrl() }}" rel="next" class="join-item btn btn-soft btn-xs sm:btn-md"
						aria-label="Next page">
						<span aria-hidden="true">»</span>
					</a>
				</li>
			@else
				<li>
					<span class="join-item btn btn-soft btn-disabled btn-xs sm:btn-md" aria-disabled="true" aria-label="Next page">
						<span aria-hidden="true">»</span>
					</span>
				</li>
			@endif
		</ul>
	</nav>
@endif
