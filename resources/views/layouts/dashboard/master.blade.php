<!DOCTYPE html>
<html lang="id">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>@yield('title', 'Elips')</title>

		@vite(['resources/css/app.css', 'resources/js/app.js'])

		{{-- Script Toaster --}}
		@if (session('success'))
			<script>
				window.addEventListener('load', function() {
					window.notyf.success(@json(session('success')));
				});
			</script>
		@endif

		@if (session('error'))
			<script>
				window.addEventListener('load', function() {
					window.notyf.error(@json(session('error')));
				});
			</script>
		@endif
	</head>

	<body class="h-screen overflow-hidden">

		<div class="flex h-full">
			@include('layouts.dashboard.sidebar')
			<div class="flex flex-col flex-1 min-w-0 overflow-y-auto">

				@include('layouts.dashboard.navbar')

				<main class="px-4 py-4 flex-1">
					@yield('content')
				</main>

			</div>
		</div>

		{{-- Script Modal Delete --}}
		<script>
			document.addEventListener('click', function(event) {
				const trigger = event.target.closest('[data-delete-url]');
				if (!trigger) return;

				const form = document.getElementById('confirm-delete-form');
				if (form) {
					form.action = trigger.getAttribute('data-delete-url');
				}
			});
		</script>

		@stack('script')

	</body>

</html>
