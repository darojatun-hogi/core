@extends('layouts.dashboard.master')
@section('title', 'Show Role')
@section('content')
	<div class="max-w-2xl mx-auto my-8 p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
		<div class="flex items-center justify-between pb-4 mb-6 border-b border-base-200">
			<div>
				<h2 class="text-2xl font-bold text-base-content">Role Details</h2>
				<p class="text-sm text-base-content/70">Overview of role and its full permission list.</p>
			</div>
			<div class="flex gap-2">
				<a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning btn-sm">Edit</a>
				<a href="{{ route('roles.index') }}" class="btn btn-ghost btn-sm">Back to List</a>
			</div>
		</div>

		<div class="space-y-4">
			<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Role Name</span>
				<span class="text-sm font-medium text-base-content">{{ $role->name }}</span>
			</div>

			<div class="p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Permissions
					({{ $role->permissions->count() }})</span>
				<div class="flex flex-wrap gap-1 mt-2">
					@forelse ($role->permissions as $permission)
						<span class="badge badge-soft badge-primary">{{ $permission->name }}</span>
					@empty
						<span class="text-xs text-base-content/50">No permissions assigned</span>
					@endforelse
				</div>
			</div>
		</div>
	</div>
@endsection
