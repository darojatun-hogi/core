@extends('layouts.dashboard.master')
@section('title', 'Create Role')
@section('content')
	<div class="max-w-2xl mx-auto my-8 p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
		<div class="mb-6">
			<h2 class="text-2xl font-bold text-base-content">Add New Role</h2>
			<p class="text-sm text-base-content/70">Enter the role's details below.</p>
		</div>

		<form action="{{ route('roles.store') }}" method="POST" class="space-y-4">
			@csrf

			<div class="form-control w-full">
				<label class="label" for="name">
					<span class="label-text font-medium">Role Name</span>
				</label>
				<input type="text" id="name" name="name" placeholder="Enter role name" value="{{ old('name') }}"
					class="input input-bordered w-full @error('name') is-invalid @enderror" required />
				@error('name')
					<span class="text-error text-xs mt-1">{{ $message }}</span>
				@enderror
			</div>

			<div class="form-control w-full">
				<label class="label">
					<span class="label-text font-medium">Permissions</span>
				</label>
				<div class="grid grid-cols-2 gap-2 rounded-box border border-base-content/40 p-3">
					@foreach ($permissions as $permission)
						<label class="flex items-center gap-2 text-sm">
							<input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="checkbox checkbox-sm">
							{{ $permission->name }}
						</label>
					@endforeach
				</div>
			</div>

			<div class="flex items-center justify-end gap-3 pt-4 border-t border-base-200">
				<a href="{{ route('roles.index') }}" class="btn btn-ghost">Cancel</a>
				<button type="submit" class="btn btn-primary">Save Role</button>
			</div>
		</form>
	</div>
@endsection
