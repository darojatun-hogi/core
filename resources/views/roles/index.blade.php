@extends('layouts.dashboard.master')
@section('title', 'Role')
@section('content')
	<div class="flex w-full flex-col gap-4">
		<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
			<div>
				<h1 class="text-2xl font-semibold">Role List</h1>
				<p class="text-sm text-base-content/70">
					Total record: <span class="font-semibold text-primary">{{ $roles->total() }}</span>
				</p>
			</div>
			<div class="flex gap-2">
				@if (auth()->user()->hasRole('Super Admin'))
					<button type="button" class="btn btn-secondary" aria-haspopup="dialog" aria-expanded="false"
						aria-controls="permission-modal" data-overlay="#permission-modal">
						<span class="icon-[tabler--key] size-4"></span>
						Manage Permission
					</button>
				@endif
				<a href="{{ route('roles.create') }}" class="btn btn-primary">Add New Role</a>
			</div>
		</div>

		<div class="rounded-box border border-base-300 bg-base-100 p-3 shadow-sm">
			<form action="{{ route('roles.index') }}" method="GET" class="flex w-full flex-row items-center gap-2">
				<input type="search" name="search" value="{{ $search }}" placeholder="Search by role name"
					class="input input-bordered h-10 w-full min-w-0 flex-1">
				<div class="flex shrink-0 gap-2">
					<button type="submit" class="btn btn-primary btn-sm h-10">Search</button>
					@if ($search)
						<a href="{{ route('roles.index') }}" class="btn btn-ghost btn-sm h-10">Clear</a>
					@endif
				</div>
			</form>
		</div>

		@if ($roles->count())
			<div class="rounded-box border border-base-300 bg-base-100 p-3 shadow-sm">
				<div class="overflow-x-auto">
					<table class="table table-zebra w-full min-w-[720px]">
						<thead>
							<tr>
								<th>#</th>
								<th>Role Name</th>
								<th>Permissions</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($roles as $role)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $role->name }}</td>
									<td class="min-w-[240px]">
										<div class="flex flex-wrap gap-1">
											@forelse ($role->permissions->take(3) as $permission)
												<span class="badge badge-soft badge-primary">{{ $permission->name }}</span>
											@empty
												<span class="text-xs text-base-content/50">No permissions</span>
											@endforelse
											@if ($role->permissions->count() > 3)
												<span class="badge badge-soft">+{{ $role->permissions->count() - 3 }} more</span>
											@endif
										</div>
									</td>
									<td class="min-w-[200px] md:min-w-[280px]">
										<div class="flex flex-row flex-wrap items-center justify-center gap-1.5">
											<a href="{{ route('roles.show', $role) }}" class="btn btn-info btn-xs sm:btn-sm">Show</a>
											<a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-xs sm:btn-sm">Edit</a>
											@if ($role->name !== 'Super Admin')
												<form action="{{ route('roles.destroy', $role) }}" method="POST"
													onsubmit="return confirm('Are you sure?');" class="inline-block">
													@csrf
													@method('DELETE')
													<button type="submit" class="btn btn-error btn-xs sm:btn-sm">Delete</button>
												</form>
											@endif
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>

			<div class="mt-2 flex justify-center xl:justify-between px-2">
				{{ $roles->links('vendor.pagination.flyonui') }}
			</div>
		@else
			<div class="alert alert-info">
				No roles found for your search.
			</div>
		@endif
	</div>

	@if (auth()->user()->hasRole('Super Admin'))
		{{-- Manage Permission Modal --}}
		<div id="permission-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden"
			role="dialog" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title">Manage Permission</h3>
						<button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close"
							data-overlay="#permission-modal">
							<span class="icon-[tabler--x] size-4"></span>
						</button>
					</div>
					<div class="modal-body">
						<form action="{{ route('permissions.store') }}" method="POST" class="flex gap-2 mb-4">
							@csrf
							<input type="text" name="name" placeholder="e.g. users.view" class="input input-bordered flex-1" required>
							<button type="submit" class="btn btn-primary shrink-0">Add</button>
						</form>
						@error('name')
							<p class="text-error text-xs mb-3">{{ $message }}</p>
						@enderror
						@if (session('error'))
							<p class="text-error text-xs mb-3">{{ session('error') }}</p>
						@endif

						<div class="max-h-64 overflow-y-auto rounded-box border border-base-content/30">
							<table class="table table-sm">
								<tbody>
									@foreach ($permissions as $permission)
										<tr>
											<td>{{ $permission->name }}</td>
											<td class="text-end">
												<form action="{{ route('permissions.destroy', $permission) }}" method="POST"
													onsubmit="return confirm('Delete this permission?');" class="inline-block">
													@csrf
													@method('DELETE')
													<button type="submit" class="btn btn-error btn-xs">Delete</button>
												</form>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection
