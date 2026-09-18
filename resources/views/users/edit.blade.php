@extends('layouts.dashboard.master')
@section('title', 'Edit User')
@section('content')
	<div class="max-w-2xl mx-auto my-8 p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
		<div class="mb-6">
			<h2 class="text-2xl font-bold text-base-content">Edit User</h2>
			<p class="text-sm text-base-content/70">Update the user's information below.</p>
		</div>

		<form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
			@csrf
			@method('PUT')

			<!-- Full name -->
			<div class="form-control w-full">
				<label class="label" for="name">
					<span class="label-text font-medium">Full Name</span>
				</label>
				<input type="text" id="name" name="name" placeholder="Enter full name"
					value="{{ old('name', $user->name) }}" class="input input-bordered w-full @error('name') is-invalid @enderror"
					required />
				@error('name')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<!-- Username -->
			<div class="form-control w-full">
				<label class="label" for="username">
					<span class="label-text font-medium">Username</span>
				</label>
				<input type="text" id="username" name="username" placeholder="Enter username"
					value="{{ old('username', $user->username) }}"
					class="input input-bordered w-full @error('username') is-invalid @enderror" required />
				@error('username')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<!-- Email -->
			<div class="form-control w-full">
				<label class="label" for="email">
					<span class="label-text font-medium">Email</span>
				</label>
				<input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
					class="input input-bordered w-full @error('email') is-invalid @enderror" placeholder="Enter email" required />
				@error('email')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<!-- Password -->
			<div class="form-control w-full">
				<label class="label" for="password">
					<span class="label-text font-medium">Password</span>
				</label>
				<input type="password" id="password" name="password"
					class="input input-bordered w-full @error('password') is-invalid @enderror" placeholder="Enter new password" />
				@error('password')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<!-- Confirm Password -->
			<div class="form-control w-full">
				<label class="label" for="password_confirmation">
					<span class="label-text font-medium">Confirm Password</span>
				</label>
				<input type="password" id="password_confirmation" name="password_confirmation"
					class="input input-bordered w-full @error('password_confirmation') is-invalid @enderror"
					placeholder="Confirm new password" />
				@error('password_confirmation')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<!-- Action Buttons -->
			<div class="flex items-center justify-end gap-3 pt-4 border-t border-base-200">
				<a href="{{ route('users.index') }}" class="btn btn-ghost">
					Cancel
				</a>
				<button type="submit" class="btn btn-primary">
					Update User
				</button>
			</div>
		</form>
	</div>
@endsection
