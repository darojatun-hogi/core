@extends('layouts.auth.master')
@section('title', 'Reset Password')
@section('content')
	<div class="max-w-md my-8 p-6 bg-base-100">
		<div class="mb-6">
			<h2 class="text-2xl font-bold text-base-content">Reset Password</h2>
			<p class="text-sm text-base-content/70">Enter your new password below.</p>
		</div>

		<form action="{{ route('password.update') }}" method="POST" class="space-y-4">
			@csrf

			<input type="hidden" name="token" value="{{ $token }}">

			<div class="form-control w-full">
				<label class="label" for="email">
					<span class="label-text font-medium">Email</span>
				</label>
				<input type="email" id="email" name="email" value="{{ old('email', $email) }}"
					class="input input-bordered w-full @error('email') is-invalid @enderror" required autofocus>
				@error('email')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<div class="form-control w-full">
				<label class="label" for="password">
					<span class="label-text font-medium">New Password</span>
				</label>
				<input type="password" id="password" name="password"
					class="input input-bordered w-full @error('password') is-invalid @enderror" required>
				@error('password')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<div class="form-control w-full">
				<label class="label" for="password_confirmation">
					<span class="label-text font-medium">Confirm New Password</span>
				</label>
				<input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full"
					required>
			</div>

			<button type="submit" class="btn btn-primary w-full">Reset Password</button>
		</form>
	</div>
@endsection
