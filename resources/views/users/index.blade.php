@extends('layouts.dashboard.master')
@section('title', 'User')
@section('content')
	<div class="flex w-full flex-col gap-4">
		<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
			<div>
				<h1 class="text-2xl font-semibold">User List</h1>
				<p class="text-sm text-base-content/70">
					Total record: <span class="font-semibold text-primary">{{ $users->total() }}</span>
				</p>
			</div>
			@can('users.create')
				<a href="{{ route('users.create') }}" class="btn btn-primary">Add New User</a>
			@endcan
		</div>

		<div class="rounded-box border border-base-300 bg-base-100 p-3 shadow-sm">
			<form action="{{ route('users.index') }}" method="GET" class="flex w-full flex-row items-center gap-2">
				<input type="search" name="search" value="{{ $search }}" placeholder="Search by name or email"
					class="input input-bordered h-10 w-full min-w-0 flex-1">
				<div class="flex shrink-0 gap-2">
					<button type="submit" class="btn btn-primary btn-sm h-10">Search</button>
					@if ($search)
						<a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm h-10">Clear</a>
					@endif
				</div>
			</form>
		</div>

		@if ($users->count())
			<div class="rounded-box border border-base-300 bg-base-100 p-3 shadow-sm">
				<div class="mb-3 flex flex-col gap-1 text-sm text-base-content/70 flex-row items-center justify-between xl:hidden">
					<span>
						Showing {{ $users->firstItem() ?: 0 }} - {{ $users->lastItem() ?: 0 }} of {{ $users->total() }} users
					</span>
					<span class="badge badge-soft badge-primary">Page {{ $users->currentPage() }}</span>
				</div>

				<div class="overflow-x-auto">
					<table class="table table-zebra w-full min-w-[720px]">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Username</th>
								<th>Email</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($users as $user)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $user->name }}</td>
									<td>{{ $user->username }}</td>
									<td>{{ $user->email }}</td>
									<td class="min-w-[200px] md:min-w-[280px]">
										<div class="flex flex-row flex-wrap items-center justify-center gap-1.5">
											@can('users.view')
												<a href="{{ route('users.show', $user) }}" class="btn btn-info btn-xs sm:btn-sm">View</a>
											@endcan
											@can('update', $user)
												<a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-xs sm:btn-sm">Edit</a>
											@endcan
											@can('delete', $user)
												<button type="button" class="btn btn-error btn-xs sm:btn-sm" aria-haspopup="dialog" aria-expanded="false"
													aria-controls="confirm-delete-modal" data-overlay="#confirm-delete-modal"
													data-delete-url="{{ route('users.destroy', $user) }}">
													Delete
												</button>
											@endcan
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>

			<div class="mt-2 flex justify-center xl:justify-between px-2">
				{{ $users->links('vendor.pagination.flyonui') }}
			</div>
		@else
			<div class="alert alert-info">
				No users found for your search.
			</div>
		@endif
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
