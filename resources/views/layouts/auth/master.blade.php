<!DOCTYPE html>
<html lang="id">

	<head>
		@include('partials.head')
		@if (config('services.turnstile.site_key'))
			<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
		@endif
	</head>

	<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
		<div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
			<a href="{{ url('/') }}" class="mb-8 text-2xl font-bold tracking-tight text-base-content">Elips</a>

			<div class="w-full max-w-md rounded-box border border-base-content/10 bg-base-100 p-6 sm:p-8">
				@yield('content')
			</div>

			@hasSection('footer')
				<div class="mt-6 text-center text-sm text-base-content/70">
					@yield('footer')
				</div>
			@endif
		</div>
	</body>

</html>
