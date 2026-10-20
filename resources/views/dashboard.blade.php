@extends('layouts.dashboard.master')
@section('title', 'Home')
@section('content')
	<div class="flex flex-col gap-4">
		<h1 class="text-2xl font-semibold">Hello World</h1>

		{{-- Hub SSO: daftar app yang bisa dibuka dengan akun yang sama --}}
		@if (config('sso.hub'))
			<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
				@foreach (config('sso.apps') as $app)
					<a href="{{ $app['url'] }}" class="card bg-base-100 shadow-sm transition hover:shadow-md">
						<div class="card-body flex-row items-center gap-4">
							<span class="{{ $app['icon'] }} size-8 text-primary"></span>
							<div>
								<h2 class="card-title">{{ $app['name'] }}</h2>
								<p class="text-sm text-base-content/60">{{ parse_url($app['url'], PHP_URL_HOST) }}</p>
							</div>
						</div>
					</a>
				@endforeach
			</div>
		@endif
	</div>
@endsection
