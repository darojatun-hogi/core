@extends('layouts.auth.master')
@section('content')
	<div class="mb-6 text-center">
		<h1 class="text-2xl font-semibold text-base-content">Login</h1>
		<p class="mt-1 text-sm text-base-content/60">
			Input your email and password to access your account.
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

		{{-- Email or Username --}}
		<div class="form-control w-full">
			<label for="login" class="label-text mb-1.5 block font-medium">
				Email or Username
			</label>
			<input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
				placeholder="john@example.com" class="input @error('login') is-invalid @enderror w-full" />
			@error('login')
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
		<div class="flex items-center justify-between">
			<label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
				<input id="remember_me" type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
				<span class="text-sm label-text">Remember me</span>
			</label>

			@if (Route::has('password.request'))
				<a href="{{ route('password.request') }}" class="text-sm link link-primary">
					Forgot your password?
				</a>
			@endif
		</div>

		{{-- Submit --}}
		<button type="submit" class="btn btn-primary w-full mt-2">
			Login
		</button>

		@if (Route::has('register'))
			<p class="mt-4 text-center text-sm text-base-content/60">
				Don't have an account?
				<a href="{{ route('register') }}" class="link link-primary font-medium">
					Register
				</a>
			</p>
		@endif
	</form>
@endsection
