@extends('layouts.dashboard.master')
@section('title', 'My Profile')
@section('content')
	<div class="flex flex-col gap-6 max-w-2xl mx-auto my-8">

		@if (session('success'))
			<div
				class="alert alert-soft alert-success removing:translate-x-5 removing:opacity-0 flex items-center gap-4 transition duration-300 ease-in-out"
				role="alert" id="dismiss-alert-success">
				<span class="icon-[tabler--circle-check] shrink-0 size-5"></span>
				<span>{{ session('success') }}</span>
				<button class="ms-auto cursor-pointer leading-none" data-remove-element="#dismiss-alert-success"
					aria-label="Close Button">
					<span class="icon-[tabler--x] size-5"></span>
				</button>
			</div>
		@endif

		<!-- Profile Info -->
		<div class="p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
			<div class="mb-6">
				<h2 class="text-2xl font-bold text-base-content">Profile Information</h2>
				<p class="text-sm text-base-content/70">Update your account's name, username, and email.</p>
			</div>

			<form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
				@csrf
				@method('PUT')

				<div class="form-control w-full">
					<label class="label" for="name">
						<span class="label-text font-medium">Full Name</span>
					</label>
					<input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
						class="input input-bordered w-full @error('name') is-invalid @enderror" required>
					@error('name')
						<span class="text-error text-xs mt-1">{{ $message }}</span>
					@enderror
				</div>

				<div class="form-control w-full">
					<label class="label" for="username">
						<span class="label-text font-medium">Username</span>
					</label>
					<input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
						class="input input-bordered w-full @error('username') is-invalid @enderror" required>
					@error('username')
						<span class="text-error text-xs mt-1">{{ $message }}</span>
					@enderror
				</div>

				<div class="form-control w-full">
					<label class="label" for="email">
						<span class="label-text font-medium">Email</span>
					</label>
					<input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
						class="input input-bordered w-full @error('email') is-invalid @enderror" required>
					@error('email')
						<span class="text-error text-xs mt-1">{{ $message }}</span>
					@enderror
				</div>

				<div class="flex justify-end pt-4 border-t border-base-200">
					<button type="submit" class="btn btn-primary">Save Changes</button>
				</div>
			</form>
		</div>

		<!-- Update Password -->
		<div class="p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
			<div class="mb-6">
				<h2 class="text-2xl font-bold text-base-content">Update Password</h2>
				<p class="text-sm text-base-content/70">Make sure your account uses a long, random password.</p>
			</div>

			<form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
				@csrf
				@method('PUT')

				<div class="form-control w-full">
					<label class="label" for="current_password">
						<span class="label-text font-medium">Current Password</span>
					</label>
					<input type="password" id="current_password" name="current_password"
						class="input input-bordered w-full @error('current_password') is-invalid @enderror" required>
					@error('current_password')
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

				<div class="flex justify-end pt-4 border-t border-base-200">
					<button type="submit" class="btn btn-primary">Update Password</button>
				</div>
			</form>
		</div>
	</div>
@endsection
