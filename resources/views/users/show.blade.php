@extends('layouts.dashboard.master')
@section('title', 'Show User')
@section('content')
	<div class="max-w-2xl mx-auto my-8 p-6 bg-base-100 rounded-xl shadow-md border border-base-200">
		<!-- Header -->
		<div class="flex items-center justify-between pb-4 mb-6 border-b border-base-200">
			<div>
				<h2 class="text-2xl font-bold text-base-content">User Details</h2>
				<p class="text-sm text-base-content/70">Overview of user's profile information.</p>
			</div>
			<div class="flex gap-2">
				@can('update', $user)
					<a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
						Edit
					</a>
				@endcan
				<a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm">
					Back to List
				</a>
			</div>
		</div>

		<!-- Details Content -->
		<div class="space-y-4">
			<!-- User ID -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">User ID</span>
				<span class="font-mono text-sm font-bold text-primary">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
			</div>

			<!-- Full Name -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Full Name</span>
				<span class="text-sm font-medium text-base-content">{{ $user->name }}</span>
			</div>

			<!-- Username -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Username</span>
				<span class="text-sm font-medium text-base-content">{{ $user->username }}</span>
			</div>

			<!-- Email -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Email</span>
				<span class="text-sm font-medium text-base-content">{{ $user->email }}</span>
			</div>

			<!-- Role -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-base-200/50">
				<span class="text-xs font-semibold uppercase tracking-wider text-base-content/60">Role</span>
				<span class="flex gap-1">
					@forelse ($user->roles as $role)
						<span class="badge badge-soft badge-primary">{{ $role->name }}</span>
					@empty
						<span class="text-sm text-base-content/50">No role assigned</span>
					@endforelse
				</span>
			</div>

			<!-- Metadata (Created / Updated) -->
			<div class="grid grid-cols-2 gap-2 pt-2 text-xs text-base-content/50">
				<div>Created: {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}</div>
				<div class="text-right">Last Updated: {{ $user->updated_at?->format('d M Y, H:i') ?? '-' }}</div>
			</div>
		</div>

		<!-- Delete Action Modal Trigger -->
		<div class="mt-8 pt-4 border-t border-base-200 flex justify-end items-center">
			@can('delete', $user)
				<button type="button" class="btn btn-error btn-outline btn-xs" aria-haspopup="dialog" aria-expanded="false"
					aria-controls="confirm-delete-modal" data-overlay="#confirm-delete-modal"
					data-delete-url="{{ route('users.destroy', $user) }}">
					Delete User
				</button>
			@endcan
		</div>
	</div>

	<!-- Delete Confirmation Modal -->
	<div id="confirm-delete-modal"
		class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog"
		tabindex="-1">
		<div class="modal-dialog modal-dialog-sm">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title">Delete User</h3>
					<button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close"
						data-overlay="#confirm-delete-modal">
						<span class="icon-[tabler--x] size-4"></span>
					</button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete this user? This action cannot be undone.
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-soft btn-secondary" data-overlay="#confirm-delete-modal">Cancel</button>
					<form id="confirm-delete-form" method="POST">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn btn-error">Delete</button>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection
