@extends('layouts.dashboard.master')
@section('title', 'Role & Permission')
@section('content')
	<div class="flex flex-col gap-6">
		<x-page-header title="Role & Permission" description="Role mengelompokkan permission. Beri role ke pengguna di halaman Pengguna.">
			<x-slot:actions>
				@can('managePermissions', \App\Models\Role::class)
					<button type="button" class="btn btn-soft" aria-haspopup="dialog" aria-expanded="false"
						aria-controls="permission-modal" data-overlay="#permission-modal">
						<span class="icon-[tabler--key] size-5"></span>
						Kelola permission
					</button>
				@endcan
				@can('create', \App\Models\Role::class)
					<a href="{{ route('roles.create') }}" class="btn btn-primary">
						<span class="icon-[tabler--plus] size-5"></span>
						Tambah role
					</a>
				@endcan
			</x-slot:actions>
		</x-page-header>

		@include('partials.managed-app')

		<div class="overflow-hidden rounded-box border border-base-content/10 bg-base-100">
			<form action="{{ route('roles.index') }}" method="GET" role="search"
				class="flex flex-col gap-3 border-b border-base-content/10 p-4 sm:flex-row sm:items-center">
				<label class="input w-full min-w-0 items-center gap-2 sm:flex-1">
					<span class="icon-[tabler--search] size-5 text-base-content/60"></span>
					<span class="sr-only">Cari role</span>
					<input type="search" name="search" value="{{ $search }}" placeholder="Cari nama role" class="grow" />
				</label>
				<div class="flex items-center justify-between gap-3 sm:justify-end">
					<span class="text-sm text-base-content/70">{{ $roles->total() }} role</span>
					@if ($search)
						<a href="{{ route('roles.index') }}" class="btn btn-text btn-sm">Hapus pencarian</a>
					@endif
				</div>
			</form>

			@if ($roles->count())
				<div class="overflow-x-auto">
					<table class="table">
						<thead>
							<tr>
								<th>Role</th>
								<th class="max-sm:hidden">Permission</th>
								<th class="w-px text-end"><span class="sr-only">Aksi</span></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($roles as $role)
								@php($locked = $role->name === config('access.super_admin_role'))
								<tr class="hover:bg-base-200/60">
									<td class="whitespace-nowrap">
										<span class="font-medium text-base-content">{{ $role->name }}</span>
										@if ($locked)
											<span class="icon-[tabler--lock] ms-1 size-4 align-[-2px] text-base-content/60"
												title="Role bawaan, tidak bisa diubah" aria-label="Role bawaan, tidak bisa diubah"></span>
										@endif
										<div class="text-sm text-base-content/70 sm:hidden">{{ $role->permissions->count() }} permission</div>
									</td>
									<td class="min-w-60 max-sm:hidden">
										<div class="flex flex-wrap items-center gap-1">
											@forelse ($role->permissions->take(3) as $permission)
												<span class="badge badge-soft badge-sm">{{ $permission->name }}</span>
											@empty
												<span class="text-sm text-base-content/70">Belum ada permission</span>
											@endforelse
											@if ($role->permissions->count() > 3)
												<span class="text-sm text-base-content/70">+{{ $role->permissions->count() - 3 }} lainnya</span>
											@endif
										</div>
									</td>
									<td>
										<div class="flex items-center justify-end gap-1">
											@can('view', $role)
												<a href="{{ route('roles.show', $role) }}" class="btn btn-text btn-square btn-sm"
													aria-label="Lihat {{ $role->name }}" title="Lihat">
													<span class="icon-[tabler--eye] size-5"></span>
												</a>
											@endcan
											@unless ($locked)
												@can('update', $role)
													<a href="{{ route('roles.edit', $role) }}" class="btn btn-text btn-square btn-sm"
														aria-label="Ubah {{ $role->name }}" title="Ubah">
														<span class="icon-[tabler--pencil] size-5"></span>
													</a>
												@endcan
												@can('delete', $role)
													<button type="button" class="btn btn-text btn-square btn-sm text-error" aria-haspopup="dialog"
														aria-expanded="false" aria-controls="confirm-delete-modal" data-overlay="#confirm-delete-modal"
														data-delete-url="{{ route('roles.destroy', $role) }}" aria-label="Hapus {{ $role->name }}"
														title="Hapus">
														<span class="icon-[tabler--trash] size-5"></span>
													</button>
												@endcan
											@endunless
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@elseif ($search)
				<x-empty-state icon="icon-[tabler--search]" title="Tidak ada role “{{ $search }}”"
					description="Periksa ejaan, nama role biasanya diawali nama aplikasi, misalnya {{ \App\Models\Role::appPrefix(\App\Support\ManagedApp::current()) }}admin.">
					<a href="{{ route('roles.index') }}" class="btn btn-soft btn-sm">Tampilkan semua role</a>
				</x-empty-state>
			@else
				<x-empty-state icon="icon-[tabler--key]" title="Belum ada role untuk aplikasi ini"
					description="Buat role, pilih permission-nya, lalu berikan ke pengguna.">
					@can('create', \App\Models\Role::class)
						<a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">Tambah role</a>
					@endcan
				</x-empty-state>
			@endif
		</div>

		{{ $roles->links('vendor.pagination.flyonui') }}
	</div>

	<x-confirm-modal title="Hapus role">
		Pengguna yang memakai role ini akan kehilangan permission-nya. Tindakan ini tidak bisa dibatalkan.
	</x-confirm-modal>

	@can('managePermissions', \App\Models\Role::class)
		{{-- Modal kelola permission --}}
		<div id="permission-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden"
			role="dialog" tabindex="-1" aria-labelledby="permission-modal-title">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h3 id="permission-modal-title" class="modal-title">Kelola permission</h3>
						<button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Tutup"
							data-overlay="#permission-modal">
							<span class="icon-[tabler--x] size-4"></span>
						</button>
					</div>
					<div class="modal-body">
						<form action="{{ route('permissions.store') }}" method="POST">
							@csrf
							<label for="permission-name" class="label-text mb-1.5 block font-medium">Permission baru</label>
							<div class="flex gap-2">
								<input id="permission-name" type="text" name="name"
									placeholder="{{ \App\Models\Role::appPrefix(\App\Support\ManagedApp::current()) }}nama.aksi"
									@class(['input min-w-0 flex-1', 'is-invalid' => $errors->has('name')]) required>
								<button type="submit" class="btn btn-primary shrink-0">Tambah</button>
							</div>
							@error('name')
								<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
							@else
								<p class="mt-1.5 text-sm text-base-content/70">
									Wajib diawali {{ \App\Models\Role::appPrefix(\App\Support\ManagedApp::current()) }}
								</p>
							@enderror
						</form>
						@if (session('error'))
							<p class="mt-3 text-sm text-error">{{ session('error') }}</p>
						@endif

						<div class="mt-4 max-h-72 overflow-y-auto rounded-box border border-base-content/10">
							@forelse ($permissions as $permission)
								<div class="flex items-center justify-between gap-2 border-b border-base-content/10 px-3 py-2 last:border-b-0">
									<span class="min-w-0 break-all text-sm">{{ $permission->name }}</span>
									<button type="button" class="btn btn-text btn-square btn-xs text-error"
										data-permission-delete-url="{{ route('permissions.destroy', $permission) }}"
										aria-label="Hapus permission {{ $permission->name }}" title="Hapus">
										<span class="icon-[tabler--trash] size-4"></span>
									</button>
								</div>
							@empty
								<p class="px-3 py-6 text-center text-sm text-base-content/70">Belum ada permission. Tambahkan lewat form di atas.</p>
							@endforelse
						</div>
					</div>
				</div>
			</div>
		</div>

		{{-- Konfirmasi hapus permission (sibling, bukan nested di dalam #permission-modal) --}}
		<x-confirm-modal id="confirm-delete-permission-modal" form-id="confirm-delete-permission-form" title="Hapus permission">
			Role yang memakai permission ini akan kehilangan akses tersebut. Tindakan ini tidak bisa dibatalkan.
		</x-confirm-modal>

		{{-- Pemicu tersembunyi, dipakai JS untuk membuka modal konfirmasi secara terprogram --}}
		<button type="button" id="open-confirm-delete-permission-trigger" data-overlay="#confirm-delete-permission-modal"
			class="hidden" tabindex="-1" aria-hidden="true"></button>
	@endcan
@endsection

@push('script')
	<script>
		document.addEventListener('click', function(event) {
			const permissionDeleteTrigger = event.target.closest('[data-permission-delete-url]');
			if (!permissionDeleteTrigger) return;

			const permissionForm = document.getElementById('confirm-delete-permission-form');
			if (permissionForm) {
				permissionForm.action = permissionDeleteTrigger.getAttribute('data-permission-delete-url');
			}

			// Tutup modal "Kelola permission" dulu (klik tombol close bawaannya)
			const closePermissionModalBtn = document.querySelector(
				'#permission-modal [data-overlay="#permission-modal"]');
			if (closePermissionModalBtn) {
				closePermissionModalBtn.click();
			}

			// Beri jeda untuk animasi tutup, baru buka modal konfirmasi hapus
			setTimeout(function() {
				const openConfirmTrigger = document.getElementById('open-confirm-delete-permission-trigger');
				if (openConfirmTrigger) {
					openConfirmTrigger.click();
				}
			}, 200);
		});

		// Buka lagi modal permission kalau ada error validasi dari form tambah permission
		@if ($errors->has('name') || session('error'))
			window.addEventListener('load', function() {
				const trigger = document.querySelector('[aria-controls="permission-modal"]');
				if (trigger) trigger.click();
			});
		@endif
	</script>
@endpush
