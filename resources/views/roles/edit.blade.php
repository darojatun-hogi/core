@extends('layouts.dashboard.master')
@section('title', 'Edit Role')
@section('content')
	<div class="max-w-2xl mx-auto my-8 p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
		<div class="mb-6">
			<h2 class="text-2xl font-bold text-base-content">Edit Role</h2>
			<p class="text-sm text-base-content/70">Update the role's information below.</p>
		</div>

		<form action="{{ route('roles.update', $role->id) }}" method="POST" class="space-y-4">
			@csrf
			@method('PUT')

			<div class="form-control w-full">
				<label class="label" for="name">
					<span class="label-text font-medium">Role Name</span>
				</label>
				<input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
					class="input input-bordered w-full @error('name') is-invalid @enderror"
					@if ($role->name === 'Super Admin') readonly @endif required />
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
							<input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="checkbox checkbox-sm"
								@checked($role->permissions->contains('name', $permission->name))>
							{{ $permission->name }}
						</label>
					@endforeach
				</div>
			</div>

			<div class="flex items-center justify-end gap-3 pt-4 border-t border-base-200">
				<a href="{{ route('roles.index') }}" class="btn btn-ghost">Cancel</a>
				<button type="submit" class="btn btn-primary">Update Role</button>
			</div>
		</form>
	</div>
@endsection
