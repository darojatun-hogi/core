@extends('layouts.dashboard.master')
@section('title', $role->name)
@section('content')
	<div class="mx-auto flex max-w-2xl flex-col gap-6">
		<x-page-header :title="$role->name" :description="$role->permissions->count() . ' permission'" :back="route('roles.index')">
			@if ($role->name !== config('access.super_admin_role'))
				@can('update', $role)
					<x-slot:actions>
						<a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary">
							<span class="icon-[tabler--pencil] size-5"></span>
							Ubah
						</a>
					</x-slot:actions>
				@endcan
			@endif
		</x-page-header>

		<div class="rounded-box border border-base-content/10 bg-base-100">
			@if ($role->permissions->isEmpty())
				<x-empty-state icon="icon-[tabler--key-off]" title="Role ini belum punya permission"
					description="Pengguna dengan role ini belum bisa melakukan apa pun selain masuk." />
			@else
				<ul class="divide-y divide-base-content/10">
					@foreach ($role->permissions->sortBy('name') as $permission)
						<li class="flex items-center gap-3 px-5 py-3 sm:px-6">
							<span class="icon-[tabler--check] size-4 shrink-0 text-primary" aria-hidden="true"></span>
							<span class="break-all text-sm">{{ $permission->name }}</span>
						</li>
					@endforeach
				</ul>
			@endif
		</div>
	</div>
@endsection
