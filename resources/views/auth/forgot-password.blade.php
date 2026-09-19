@extends('layouts.auth.master')
@section('title', 'Forgot Password')
@section('content')
	<div class="max-w-md my-8 p-6 bg-base-100">
		<div class="mb-6">
			<h2 class="text-2xl font-bold text-base-content">Forgot Password</h2>
			<p class="text-sm text-base-content/70">Enter your email and we'll send you a password reset link.</p>
		</div>

		@if (session('success'))
			<div
				class="alert alert-soft alert-success removing:translate-x-5 removing:opacity-0 flex items-center gap-4 transition duration-300 ease-in-out mb-4"
				role="alert" id="dismiss-alert-success">
				<span class="icon-[tabler--circle-check] shrink-0 size-5"></span>
				<span>{{ session('success') }}</span>
				<button class="ms-auto cursor-pointer leading-none" data-remove-element="#dismiss-alert-success"
					aria-label="Close Button">
					<span class="icon-[tabler--x] size-5"></span>
				</button>
			</div>
		@endif

		<form action="{{ route('password.email') }}" method="POST" class="space-y-4">
			@csrf

			<div class="form-control w-full">
				<label class="label" for="email">
					<span class="label-text font-medium">Email</span>
				</label>
				<input type="email" id="email" name="email" value="{{ old('email') }}"
					class="input input-bordered w-full @error('email') is-invalid @enderror" required autofocus>
				@error('email')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<button type="submit" class="btn btn-primary w-full">Send Reset Link</button>

			<div class="text-center text-sm">
				<a href="{{ route('login') }}" class="link link-primary">Back to Login</a>
			</div>
		</form>
	</div>
@endsection
