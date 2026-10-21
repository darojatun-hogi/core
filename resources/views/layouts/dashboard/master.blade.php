<!DOCTYPE html>
<html lang="id">

	<head>
		@include('partials.head')
	</head>

	<body class="h-screen overflow-hidden bg-base-200 font-sans text-base-content antialiased">

		<div class="flex h-full">
			@include('layouts.dashboard.sidebar')
			<div class="flex min-w-0 flex-1 flex-col overflow-y-auto">

				@include('layouts.dashboard.navbar')

				<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
					@yield('content')
				</main>

			</div>
		</div>

		<script>
			// Isi action form modal hapus dari tombol pemicu (data-delete-url)
			document.addEventListener('click', function(event) {
				const trigger = event.target.closest('[data-delete-url]');
				if (!trigger) return;

				const form = document.getElementById('confirm-delete-form');
				if (form) {
					form.action = trigger.getAttribute('data-delete-url');
				}
			});

			// Overlay FlyonUI menangkap Enter di dalam drawer dan membatalkan aksi bawaan link/tombol sidebar.
			// Hentikan Enter/Space di sini supaya navigasi keyboard tetap jalan.
			document.getElementById('app-sidebar')?.addEventListener('keydown', function(event) {
				if ((event.key === 'Enter' || event.key === ' ') && event.target.closest('a, button')) {
					event.stopPropagation();
				}
			});

			// Ganti tema terang/gelap, disimpan per browser
			document.addEventListener('click', function(event) {
				if (!event.target.closest('[data-theme-toggle]')) return;

				const dark = document.documentElement.getAttribute('data-theme') !== 'elips-dark';
				document.documentElement.setAttribute('data-theme', dark ? 'elips-dark' : 'elips');
				try {
					localStorage.setItem('theme', dark ? 'dark' : 'light');
				} catch (e) {}
			});
		</script>

		@stack('script')

	</body>

</html>
