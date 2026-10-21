<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Elips') · Elips</title>

{{-- Pasang tema sebelum render supaya tidak berkedip saat mode gelap aktif --}}
<script>
	(function() {
		// Aktifkan animasi masuk [data-reveal] (motion.css); tanpa JS konten tetap terlihat
		document.documentElement.classList.add('motion');
		try {
			var saved = localStorage.getItem('theme');
			var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
			document.documentElement.setAttribute('data-theme', dark ? 'elips-dark' : 'elips');
		} catch (e) {
			document.documentElement.setAttribute('data-theme', 'elips');
		}
	})();
</script>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Toast dari session flash --}}
@foreach (['success' => 'success', 'error' => 'error'] as $key => $type)
	@if (session($key))
		<script>
			window.addEventListener('load', function() {
				window.notyf.{{ $type }}(@json(session($key)));
			});
		</script>
	@endif
@endforeach

@if (session('info'))
	<script>
		window.addEventListener('load', function() {
			window.notyf.open({
				type: 'info',
				message: @json(session('info'))
			});
		});
	</script>
@endif
