@extends('layouts.auth.master')
@section('content')
	<div class="mb-6 text-center">
		<h1 class="text-2xl font-semibold text-base-content">Masuk ke Akun Anda</h1>
		<p class="mt-1 text-sm text-base-content/60">
			Silakan masukkan email dan password Anda
		</p>
	</div>

	{{-- Session Status --}}
	@if (session('status'))
		<div class="alert alert-soft alert-success mb-4 text-sm">
			{{ session('status') }}
		</div>
	@endif

	<form method="POST" action="{{ route('login.authenticate') }}" class="space-y-4">
		@csrf

		{{-- Email --}}
		<div class="form-control w-full">
			<label for="email" class="label-text mb-1.5 block font-medium">
				Email
			</label>
			<input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
				autocomplete="username" placeholder="john@example.com" class="input @error('email') is-invalid @enderror w-full" />
			@error('email')
				<span class="label-text-alt text-error mt-1.5 block">
					{{ $message }}
				</span>
			@enderror
		</div>

		{{-- Password --}}
		<div class="form-control w-full">
			<div class="mb-1.5 flex items-center justify-between">
				<label for="password" class="label-text font-medium">
					Password
				</label>
				@if (Route::has('password.request'))
					<a href="{{ route('password.request') }}" class="link link-primary text-sm">
						Lupa password?
					</a>
				@endif
			</div>
			<input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
				class="input @error('password') is-invalid @enderror w-full" />
			@error('password')
				<span class="label-text-alt text-error mt-1.5 block">
					{{ $message }}
				</span>
			@enderror
		</div>

		{{-- Remember Me --}}
		<div class="flex items-center gap-2">
			<input id="remember_me" type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
			<label for="remember_me" class="label-text cursor-pointer text-sm">
				Ingat saya
			</label>
		</div>

		{{-- Submit --}}
		<button type="submit" class="btn btn-primary w-full mt-2">
			Masuk
		</button>

		@if (Route::has('register'))
			<p class="mt-4 text-center text-sm text-base-content/60">
				Belum punya akun?
				<a href="{{ route('register') }}" class="link link-primary font-medium">
					Daftar sekarang
				</a>
			</p>
		@endif
	</form>
@endsection
