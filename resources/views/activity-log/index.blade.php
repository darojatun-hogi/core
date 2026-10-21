@extends('layouts.dashboard.master')
@section('title', 'Log Aktivitas')
@section('content')
	@php
		$subjectTypes = ['App\Models\User' => 'Pengguna', 'App\Models\Role' => 'Role', 'App\Models\Permission' => 'Permission'];
		$events = ['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus'];
		$filtered = request()->filled('subject_type') || request()->filled('event');
	@endphp

	<div class="flex flex-col gap-6">
		<x-page-header title="Log aktivitas" description="Riwayat perubahan pengguna, role, dan permission di aplikasi ini." />

		<div class="overflow-hidden rounded-box border border-base-content/10 bg-base-100">
			<form action="{{ route('activity-log.index') }}" method="GET" id="activity-filter"
				class="flex flex-col gap-3 border-b border-base-content/10 p-4 sm:flex-row sm:items-end">
				<div class="sm:w-48">
					<label for="subject_type" class="label-text mb-1.5 block text-sm">Data</label>
					<select id="subject_type" name="subject_type" class="select" onchange="this.form.requestSubmit()">
						<option value="">Semua data</option>
						@foreach ($subjectTypes as $value => $label)
							<option value="{{ $value }}" @selected(request('subject_type') === $value)>{{ $label }}</option>
						@endforeach
					</select>
				</div>
				<div class="sm:w-48">
					<label for="event" class="label-text mb-1.5 block text-sm">Kejadian</label>
					<select id="event" name="event" class="select" onchange="this.form.requestSubmit()">
						<option value="">Semua kejadian</option>
						@foreach ($events as $value => $label)
							<option value="{{ $value }}" @selected(request('event') === $value)>{{ $label }}</option>
						@endforeach
					</select>
				</div>
				<noscript><button type="submit" class="btn btn-soft">Terapkan</button></noscript>
				@if ($filtered)
					<a href="{{ route('activity-log.index') }}" class="btn btn-text btn-sm sm:mb-1">Hapus filter</a>
				@endif
			</form>

			@if ($activities->count())
				<div class="overflow-x-auto">
					<table class="table">
						<thead>
							<tr>
								<th>Aktivitas</th>
								<th>Kejadian</th>
								<th class="max-md:hidden">Waktu</th>
								<th class="w-px"><span class="sr-only">Detail</span></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($activities as $activity)
								<tr class="hover:bg-base-200/60">
									<td class="min-w-56">
										<div class="text-base-content">{{ $activity->description }}</div>
										<div class="text-sm text-base-content/70">
											oleh {{ $activity->causer?->name ?? 'sistem' }}
											<span class="md:hidden">· {{ $activity->created_at->diffForHumans() }}</span>
										</div>
									</td>
									<td>
										<span
											class="badge badge-soft badge-sm {{ match ($activity->event) {
											    'created' => 'badge-success',
											    'updated' => 'badge-warning',
											    'deleted' => 'badge-error',
											    default => '',
											} }}">
											{{ $events[$activity->event] ?? $activity->event }}
										</span>
									</td>
									<td class="whitespace-nowrap text-sm text-base-content/70 max-md:hidden">
										<time datetime="{{ $activity->created_at->toIso8601String() }}"
											title="{{ $activity->created_at->translatedFormat('d M Y, H:i') }}">
											{{ $activity->created_at->diffForHumans() }}
										</time>
									</td>
									<td>
										@if ($activity->properties->isNotEmpty())
											<button type="button" class="btn btn-text btn-sm" aria-haspopup="dialog" aria-expanded="false"
												aria-controls="detail-{{ $activity->id }}" data-overlay="#detail-{{ $activity->id }}">
												Detail
											</button>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@elseif ($filtered)
				<x-empty-state icon="icon-[tabler--filter-off]" title="Tidak ada aktivitas dengan filter ini">
					<a href="{{ route('activity-log.index') }}" class="btn btn-soft btn-sm">Hapus filter</a>
				</x-empty-state>
			@else
				<x-empty-state icon="icon-[tabler--history]" title="Belum ada aktivitas tercatat"
					description="Setiap kali pengguna, role, atau permission dibuat, diubah, atau dihapus, catatannya muncul di sini." />
			@endif
		</div>

		{{ $activities->links('vendor.pagination.flyonui') }}
	</div>

	{{-- Modal detail perubahan, diletakkan di luar tabel --}}
	@foreach ($activities as $activity)
		@continue($activity->properties->isEmpty())
		@php
			$old = $activity->properties['old'] ?? [];
			$new = $activity->properties['attributes'] ?? [];
			$fields = collect($new)->keys()->merge(collect($old)->keys())->unique();
			$format = fn ($value) => is_array($value) ? implode(', ', $value) : ($value ?? 'kosong');
		@endphp
		<div id="detail-{{ $activity->id }}" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden"
			role="dialog" tabindex="-1" aria-labelledby="detail-{{ $activity->id }}-title">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h3 id="detail-{{ $activity->id }}-title" class="modal-title">Detail perubahan</h3>
						<button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Tutup"
							data-overlay="#detail-{{ $activity->id }}">
							<span class="icon-[tabler--x] size-4"></span>
						</button>
					</div>
					<div class="modal-body">
						<p class="mb-4 text-base-content/80">{{ $activity->description }}</p>

						@if ($fields->isEmpty())
							<p class="text-sm text-base-content/70">Tidak ada detail kolom yang tercatat.</p>
						@else
							<dl class="divide-y divide-base-content/10 rounded-box border border-base-content/10">
								@foreach ($fields as $field)
									<div class="px-4 py-3">
										<dt class="text-sm font-medium text-base-content">{{ $field }}</dt>
										<dd class="mt-1 flex flex-wrap items-center gap-2 text-sm">
											@if (array_key_exists($field, $old))
												<span class="break-all text-base-content/70 line-through">{{ $format($old[$field]) }}</span>
												<span class="icon-[tabler--arrow-right] size-4 text-base-content/60" aria-label="menjadi"></span>
											@endif
											<span class="break-all text-base-content">{{ $format($new[$field] ?? null) }}</span>
										</dd>
									</div>
								@endforeach
							</dl>
						@endif
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-soft" data-overlay="#detail-{{ $activity->id }}">Tutup</button>
					</div>
				</div>
			</div>
		</div>
	@endforeach
@endsection
