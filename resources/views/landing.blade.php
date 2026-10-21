{{-- Landing page hub SSO. Juga dibuka lewat domain induk (elips.id), jadi tautan memakai APP_URL (auth.elips.id) --}}
@section('title', 'Satu akun untuk semua aplikasi')
@php
	$hub = rtrim(config('app.url'), '/');
	$apps = config('sso.apps', []);
@endphp
<!DOCTYPE html>
<html lang="id">

	<head>
		@include('partials.head')
		<meta name="description" content="Satu akun untuk semua aplikasi Elips: Money, Clip, dan Portfolio.">
		<link rel="canonical" href="{{ $hub }}/">
	</head>

	<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
		<header class="border-b border-base-content/10 bg-base-100">
			<div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
				<a href="{{ $hub }}/" class="text-xl font-bold tracking-tight">Elips</a>
				<nav class="flex items-center gap-2" aria-label="Akun">
					@auth
						<a href="{{ $hub }}/dashboard" class="btn btn-primary btn-sm">Buka dashboard</a>
					@else
						<a href="{{ $hub }}/login" class="btn btn-text btn-sm">Masuk</a>
						<a href="{{ $hub }}/register" class="btn btn-primary btn-sm">Buat akun</a>
					@endauth
				</nav>
			</div>
		</header>

		<main>
			<section class="bg-base-100">
				<div class="mx-auto grid max-w-5xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-[1fr_auto]">
					<div>
						<p class="text-sm font-medium text-primary">elips.id</p>
						<h1 class="mt-3 max-w-2xl text-4xl font-semibold tracking-tight sm:text-5xl">
							Satu akun untuk semua aplikasi Elips.
						</h1>
						<p class="mt-5 max-w-xl text-lg text-base-content/70">
							Masuk sekali, lalu buka {{ collect($apps)->pluck('name')->join(', ', ', dan ') ?: 'aplikasi Elips' }} tanpa login
							ulang. Keluar dari satu aplikasi berarti keluar dari semuanya.
						</p>
						<div class="mt-8 flex flex-wrap gap-3">
							@auth
								<a href="{{ $hub }}/dashboard" class="btn btn-primary">
									Buka dashboard
									<span class="icon-[tabler--arrow-right] size-4" aria-hidden="true"></span>
								</a>
							@else
								<a href="{{ $hub }}/register" class="btn btn-primary">
									Buat akun gratis
									<span class="icon-[tabler--arrow-right] size-4" aria-hidden="true"></span>
								</a>
								<a href="{{ $hub }}/login" class="btn btn-outline btn-secondary">Saya sudah punya akun</a>
							@endauth
						</div>
					</div>

					{{-- Ikon tiap app, dekoratif: daftar lengkapnya ada di bagian Aplikasi --}}
					@if ($apps)
						<ul class="flex flex-wrap gap-4 lg:grid lg:grid-cols-2" aria-hidden="true">
							@foreach ($apps as $app)
								<li @class(['flex flex-col items-center gap-2', 'lg:col-span-2' => $loop->last && $loop->odd])>
									<span @class(['flex size-16 items-center justify-center rounded-box sm:size-20', $app['tone'] ?? 'bg-primary/10 text-primary'])>
										<span class="{{ $app['icon'] }} size-8 sm:size-10"></span>
									</span>
									<span class="text-xs font-medium text-base-content/70">{{ $app['name'] }}</span>
								</li>
							@endforeach
						</ul>
					@endif
				</div>
			</section>

			@if ($apps)
				<section aria-labelledby="apps-heading" class="mx-auto max-w-5xl px-4 py-14 sm:px-6">
					<h2 id="apps-heading" class="text-2xl font-semibold tracking-tight">Aplikasi</h2>
					<p class="mt-1 text-base-content/70">Semua memakai akun yang sama.</p>
					<ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
						@foreach ($apps as $app)
							<li>
								<a href="{{ $app['url'] }}"
									class="group flex h-full flex-col rounded-box border border-base-content/10 bg-base-100 p-5 transition-colors hover:border-primary/40 focus-visible:border-primary">
									<span @class(['flex size-12 items-center justify-center rounded-box', $app['tone'] ?? 'bg-primary/10 text-primary'])>
										<span class="{{ $app['icon'] }} size-6" aria-hidden="true"></span>
									</span>
									<span class="mt-4 flex items-center gap-1 font-medium">
										{{ $app['name'] }}
										<span class="icon-[tabler--arrow-up-right] size-4 text-base-content/50 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
											aria-hidden="true"></span>
									</span>
									@isset($app['description'])
										<span class="mt-1 text-sm text-base-content/70">{{ $app['description'] }}</span>
									@endisset
									<span class="mt-auto pt-4 text-xs text-base-content/50">{{ parse_url($app['url'], PHP_URL_HOST) }}</span>
								</a>
							</li>
						@endforeach
					</ul>
				</section>
			@endif

			<section aria-labelledby="steps-heading" class="border-t border-base-content/10">
				<div class="mx-auto max-w-5xl px-4 py-14 sm:px-6">
					<h2 id="steps-heading" class="text-2xl font-semibold tracking-tight">Cara mulai</h2>
					<ol class="mt-6 grid gap-6 sm:grid-cols-3">
						@foreach ([
							['Buat akun', 'Daftar dengan nama, username, dan email.'],
							['Verifikasi email', 'Klik tautan yang kami kirim ke email kamu.'],
							['Buka aplikasi', 'Akses diberikan otomatis saat pertama kali membuka aplikasi.'],
						] as $i => [$title, $text])
							<li class="flex gap-4">
								<span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-base-100 text-sm font-semibold text-primary ring-1 ring-base-content/10">
									{{ $i + 1 }}
								</span>
								<span>
									<span class="block font-medium">{{ $title }}</span>
									<span class="mt-1 block text-sm text-base-content/70">{{ $text }}</span>
								</span>
							</li>
						@endforeach
					</ol>
				</div>
			</section>
		</main>

		<footer class="border-t border-base-content/10 bg-base-100">
			<div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-2 px-4 py-6 text-sm text-base-content/60 sm:px-6">
				<span>&copy; {{ now()->year }} Elips</span>
				<a href="{{ $hub }}/forgot-password" class="link link-hover">Lupa password?</a>
			</div>
		</footer>
	</body>

</html>
