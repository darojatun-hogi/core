@extends('layouts.auth.master')
@section('content')
	<div class="mb-6 text-center">
		<h1 class="text-2xl font-semibold text-base-content">Register</h1>
		<p class="mt-1 text-sm text-base-content/60">
			Please enter your account information
		</p>
	</div>

	{{-- Session Status --}}
	@if (session('status'))
		<div class="alert alert-soft alert-success mb-4 text-sm">
			{{ session('status') }}
		</div>
	@endif

	<form method="POST" action="{{ route('register.store') }}" class="space-y-4">
		@csrf

		{{-- Name --}}
		<div class="form-control w-full">
			<label for="name" class="label-text mb-1.5 block font-medium">
				Name
			</label>
			<input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required
				autofocus class="input @error('name') is-invalid @enderror w-full" />
			@error('name')
				<span class="label-text-alt text-error mt-1.5 block">
					{{ $message }}
				</span>
			@enderror
		</div>

		{{-- Username --}}
		<div class="form-control w-full">
			<label for="username" class="label-text mb-1.5 block font-medium">
				Username
			</label>
			<input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="john_doe" required
				class="input @error('username') is-invalid @enderror w-full" />
			@error('username')
				<span class="label-text-alt text-error mt-1.5 block">
					{{ $message }}
				</span>
			@enderror
		</div>

		{{-- Email --}}
		<div class="form-control w-full">
			<label for="email" class="label-text mb-1.5 block font-medium">
				Email
			</label>
			<input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com"
				required class="input @error('email') is-invalid @enderror w-full" />
			@error('email')
				<span class="label-text-alt text-error mt-1.5 block">
					{{ $message }}
				</span>
			@enderror
		</div>

		{{-- Password --}}
		<div class="form-control w-full">
			<label for="password" class="label-text mb-1.5 block font-medium">
				Password
			</label>
			<input id="password" type="password" name="password" placeholder="••••••••" required
				class="input @error('password') is-invalid @enderror w-full" />
			@error('password')
				<span class="label-text-alt text-error mt-1.5 block">
					{{ $message }}
				</span>
			@enderror
		</div>

		{{-- Password Confirmation --}}
		<div class="form-control w-full">
			<label for="password_confirmation" class="label-text mb-1.5 block font-medium">
				Confirm Password
			</label>
			<input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required
				class="input @error('password_confirmation') is-invalid @enderror w-full" />
		</div>

		<div class="form-control w-full">
			<button type="submit" class="btn btn-primary w-full">
				Register
			</button>
		</div>
	</form>

	<p class="mt-4 text-center text-sm text-base-content/60">
		<a href="{{ route('login') }}" class="link link-primary font-medium">
			Login
		</a>
	</p>
@endsection
