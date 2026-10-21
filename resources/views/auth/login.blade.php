@extends('layouts.auth.master')
@section('title', 'Masuk')
@section('content')
	<div class="mb-6">
		<h1 class="text-2xl font-semibold tracking-tight">Masuk ke akun Elips</h1>
		<p class="mt-1 text-sm text-base-content/70">Satu akun untuk semua aplikasi Elips.</p>
	</div>

	@if (session('status'))
		<div class="alert alert-soft alert-success mb-4 text-sm" role="status">
			{{ session('status') }}
		</div>
	@endif

	<form method="POST" action="{{ route('login.authenticate') }}" class="space-y-4">
		@csrf

		<x-field name="login" label="Email atau username" required autofocus autocomplete="username"
			placeholder="email@example.com" />

		<div class="w-full">
			<div class="mb-1.5 flex items-center justify-between gap-2">
				<label for="password" class="label-text font-medium text-base-content">Password</label>
				@if (Route::has('password.request'))
					<a href="{{ route('password.request') }}" class="link link-primary text-sm">Lupa password?</a>
				@endif
			</div>
			<input id="password" type="password" name="password" required autocomplete="current-password"
				@class(['input w-full', 'is-invalid' => $errors->has('password')]) />
			@error('password')
				<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
			@enderror
		</div>

		<label for="remember_me" class="flex cursor-pointer items-center gap-2">
			<input id="remember_me" type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
			<span class="text-sm">Ingat saya di perangkat ini</span>
		</label>

		{{-- Cloudflare Turnstile (aktif bila TURNSTILE_SITE_KEY diisi) --}}
		@if (config('services.turnstile.site_key'))
			<div>
				<div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
				@error('cf-turnstile-response')
					<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
				@enderror
			</div>
		@endif

		<button type="submit" class="btn btn-primary btn-block">Masuk</button>
	</form>
@endsection

@if (Route::has('register'))
	@section('footer')
		Belum punya akun?
		<a href="{{ route('register') }}" class="link link-primary font-medium">Buat akun</a>
	@endsection
@endif
