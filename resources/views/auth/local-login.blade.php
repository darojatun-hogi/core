{{-- Halaman masuk lokal tanpa SSO (docs/lokal-tanpa-sso.md). Meniru halaman masuk auth.elips.id; sama di semua app
	Elips dan tidak memakai aset app, jadi tetap tampil walau aset belum di-build. --}}
<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title>Masuk · Elips</title>
	<style>
		/* Warna dari halaman masuk hub (auth.elips.id) */
		:root { color-scheme: dark; --bg: oklch(0.185 0.011 255); --card: oklch(0.22 0.012 255); --text: oklch(0.925 0.008 250); --muted: oklch(0.925 0.008 250 / 0.7); --line: oklch(0.925 0.008 250 / 0.1); --input-line: oklch(0.925 0.008 250 / 0.2); --primary: oklch(0.777 0.13 182); --primary-text: oklch(0.21 0.04 185); --error: oklch(0.71 0.17 22); }
		* { box-sizing: border-box; }
		body { min-height: 100vh; margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 16px; background: var(--bg); color: var(--text); font: 16px/1.5 "Instrument Sans", ui-sans-serif, system-ui, sans-serif; }
		.brand { margin-bottom: 32px; color: var(--text); font-size: 24px; font-weight: 700; letter-spacing: -0.6px; text-decoration: none; }
		.card { width: min(100%, 448px); padding: 32px; border: 1px solid var(--line); border-radius: 12px; background: var(--card); }
		h1 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -0.6px; }
		.lead { margin: 4px 0 24px; color: var(--muted); font-size: 14px; }
		.field { margin-bottom: 16px; }
		label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; }
		input[type=text], input[type=password] { width: 100%; height: 40px; padding: 0 12px; border: 1px solid var(--input-line); border-radius: 8px; background: var(--card); color: var(--text); font: inherit; }
		input[type=text]:focus, input[type=password]:focus { border-color: var(--primary); outline: 2px solid oklch(0.777 0.13 182 / 0.3); outline-offset: 0; }
		input.invalid { border-color: var(--error); }
		.error { margin: 6px 0 0; color: var(--error); font-size: 14px; }
		.check { display: flex; min-height: 44px; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; }
		.check input { width: 16px; height: 16px; accent-color: var(--primary); }
		button { width: 100%; min-height: 44px; margin-top: 16px; border: 0; border-radius: 8px; background: var(--primary); color: var(--primary-text); cursor: pointer; font: inherit; font-weight: 500; }
		button:hover { filter: brightness(1.08); }
		button:focus-visible, a:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
		.demo { width: min(100%, 448px); margin-top: 16px; padding: 14px 16px; border: 1px dashed var(--input-line); border-radius: 12px; color: var(--muted); font-size: 14px; }
		.demo strong { color: var(--text); font-weight: 600; }
		.demo ul { margin: 8px 0 0; padding: 0; list-style: none; display: grid; gap: 4px; }
		.demo code { color: var(--text); font-size: 13px; }
	</style>
</head>

<body>
	<a class="brand" href="{{ url('/') }}">Elips</a>

	<main class="card">
		<h1>Masuk ke akun Elips</h1>
		<p class="lead">Satu akun untuk semua aplikasi Elips.</p>

		<form method="POST" action="{{ route('local-login.login') }}" novalidate>
			@csrf
			<div class="field">
				<label for="login">Email atau username</label>
				<input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
					placeholder="email@example.com" @class(['invalid' => $errors->has('login')])>
				@error('login')
					<p class="error" role="alert">{{ $message }}</p>
				@enderror
			</div>
			<div class="field">
				<label for="password">Password</label>
				<input id="password" type="password" name="password" required autocomplete="current-password" @class(['invalid' => $errors->has('password')])>
				@error('password')
					<p class="error" role="alert">{{ $message }}</p>
				@enderror
			</div>
			<label class="check" for="remember">
				<input id="remember" type="checkbox" name="remember">
				Ingat saya di perangkat ini
			</label>
			<button type="submit">Masuk</button>
		</form>
	</main>

	<aside class="demo" aria-label="Akun demo lokal">
		<strong>Mode lokal: {{ config('app.name') }}.</strong>
		Login memeriksa database lokal, bukan auth.elips.id.
		@if ($demoUsers->isEmpty())
			Belum ada akun demo; jalankan <code>php artisan app:local-setup</code>.
		@else
			Akun demo, password {!! $defaultPassword ? '<code>password</code>' : 'sesuai <code>LOCAL_DEMO_PASSWORD</code>' !!}:
			<ul>
				@foreach ($demoUsers as $user)
					<li><code>{{ $user->username }}</code> · {{ $user->roles->pluck('name')->join(', ') ?: 'tanpa role' }}</li>
				@endforeach
			</ul>
		@endif
	</aside>
</body>

</html>
