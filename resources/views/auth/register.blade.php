@extends('layouts.auth.master')
@section('title', 'Buat Akun')
@section('content')
	<div class="mb-6">
		<h1 class="text-2xl font-semibold tracking-tight">Buat akun Elips</h1>
		<p class="mt-1 text-sm text-base-content/70">Akun ini dipakai untuk masuk ke semua aplikasi Elips.</p>
	</div>

	@if (session('status'))
		<div class="alert alert-soft alert-success mb-4 text-sm" role="status">
			{{ session('status') }}
		</div>
	@endif

	<form method="POST" action="{{ route('register.store') }}" class="space-y-4">
		@csrf

		<x-field name="name" label="Nama lengkap" required autofocus autocomplete="name" placeholder="Nama kamu" />
		<x-field name="username" label="Username" required autocomplete="username" placeholder="nama_pengguna"
			hint="Minimal 3 karakter: huruf, angka, tanda hubung, atau garis bawah." />
		<x-field name="email" type="email" label="Email" required autocomplete="email" placeholder="email@example.com" />

		<div class="grid gap-4 sm:grid-cols-2">
			<x-field name="password" type="password" label="Password" required autocomplete="new-password" />
			<x-field name="password_confirmation" type="password" label="Ulangi password" required
				autocomplete="new-password" />
		</div>

		{{-- Cloudflare Turnstile (aktif bila TURNSTILE_SITE_KEY diisi) --}}
		@if (config('services.turnstile.site_key'))
			<div>
				<div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
				@error('cf-turnstile-response')
					<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
				@enderror
			</div>
		@endif

		<button type="submit" class="btn btn-primary btn-block">Buat akun</button>
	</form>
@endsection

@section('footer')
	Sudah punya akun?
	<a href="{{ route('login') }}" class="link link-primary font-medium">Masuk</a>
@endsection
