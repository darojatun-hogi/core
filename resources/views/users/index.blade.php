@extends('layouts.dashboard.master')
@section('title', 'Pengguna')
@section('content')
	@php($rolePrefix = \App\Models\Role::appPrefix(\App\Support\ManagedApp::current()))

	<div class="flex flex-col gap-6">
		<x-page-header title="Pengguna" description="Siapa saja yang bisa masuk ke aplikasi ini, dan dengan role apa.">
			@can('users.create')
				<x-slot:actions>
					<a href="{{ route('users.create') }}" class="btn btn-primary">
						<span class="icon-[tabler--user-plus] size-5"></span>
						Tambah pengguna
					</a>
				</x-slot:actions>
			@endcan
		</x-page-header>

		@include('partials.managed-app')

		<div class="overflow-hidden rounded-box border border-base-content/10 bg-base-100">
			<form action="{{ route('users.index') }}" method="GET" role="search"
				class="flex flex-col gap-3 border-b border-base-content/10 p-4 sm:flex-row sm:items-center">
				<label class="input w-full min-w-0 items-center gap-2 sm:flex-1">
					<span class="icon-[tabler--search] size-5 text-base-content/60"></span>
					<span class="sr-only">Cari pengguna</span>
					<input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, username, atau email"
						class="grow" />
				</label>
				<div class="flex items-center justify-between gap-3 sm:justify-end">
					<span class="text-sm text-base-content/70">{{ $users->total() }} pengguna</span>
					@if ($search)
						<a href="{{ route('users.index') }}" class="btn btn-text btn-sm">Hapus pencarian</a>
					@endif
				</div>
			</form>

			@if ($users->count())
				<div class="overflow-x-auto">
					<table class="table">
						<thead>
							<tr>
								<th>Nama</th>
								<th class="max-md:hidden">Username</th>
								<th class="max-sm:hidden">Role</th>
								<th class="w-px text-end"><span class="sr-only">Aksi</span></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($users as $user)
								@php($appRoles = $user->roles->filter(fn ($role) => Str::startsWith($role->name, $rolePrefix) || $role->name === config('access.super_admin_role')))
								<tr class="hover:bg-base-200/60">
									<td>
										<div class="font-medium text-base-content">{{ $user->name }}</div>
										<div class="break-all text-sm text-base-content/70">{{ $user->email }}</div>
										<div class="mt-1 text-sm text-base-content/70 sm:hidden">
											{{ $appRoles->pluck('name')->implode(', ') ?: 'Akses langsung' }}
										</div>
									</td>
									<td class="text-base-content/80 max-md:hidden">{{ $user->username }}</td>
									<td class="max-sm:hidden">
										<div class="flex flex-wrap gap-1">
											@forelse ($appRoles as $role)
												<span class="badge badge-soft badge-sm">{{ $role->name }}</span>
											@empty
												<span class="text-sm text-base-content/70">Akses langsung</span>
											@endforelse
										</div>
									</td>
									<td>
										<div class="flex items-center justify-end gap-1">
											@can('users.view')
												<a href="{{ route('users.show', $user) }}" class="btn btn-text btn-square btn-sm"
													aria-label="Lihat {{ $user->name }}" title="Lihat">
													<span class="icon-[tabler--eye] size-5"></span>
												</a>
											@endcan
											@can('update', $user)
												<a href="{{ route('users.edit', $user) }}" class="btn btn-text btn-square btn-sm"
													aria-label="Ubah {{ $user->name }}" title="Ubah">
													<span class="icon-[tabler--pencil] size-5"></span>
												</a>
											@endcan
											@can('delete', $user)
												<button type="button" class="btn btn-text btn-square btn-sm text-error" aria-haspopup="dialog"
													aria-expanded="false" aria-controls="confirm-delete-modal" data-overlay="#confirm-delete-modal"
													data-delete-url="{{ route('users.destroy', $user) }}"
													aria-label="Cabut akses {{ $user->name }}" title="Cabut akses">
													<span class="icon-[tabler--user-minus] size-5"></span>
												</button>
											@endcan
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@elseif ($search)
				<x-empty-state icon="icon-[tabler--search]" title="Tidak ada yang cocok dengan “{{ $search }}”"
					description="Coba kata kunci lain, atau cari berdasarkan email.">
					<a href="{{ route('users.index') }}" class="btn btn-soft btn-sm">Tampilkan semua pengguna</a>
				</x-empty-state>
			@else
				<x-empty-state icon="icon-[tabler--users]" title="Belum ada pengguna di aplikasi ini"
					description="Tambahkan pengguna dan beri role supaya mereka bisa masuk.">
					@can('users.create')
						<a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Tambah pengguna</a>
					@endcan
				</x-empty-state>
			@endif
		</div>

		{{ $users->links('vendor.pagination.flyonui') }}
	</div>

	<x-confirm-modal title="Cabut akses pengguna" confirm="Cabut akses">
		Pengguna ini tidak bisa lagi masuk ke aplikasi ini. Akunnya dan akses ke aplikasi lain tetap ada.
	</x-confirm-modal>
@endsection
