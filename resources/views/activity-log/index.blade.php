@extends('layouts.dashboard.master')
@section('title', 'Activity Log')
@section('content')
	<div class="rounded-box border border-base-300 bg-base-100 p-3 shadow-sm">
		<div class="flex flex-wrap items-center gap-2 mb-4">
			<select name="subject_type" onchange="window.location.href='{{ route('activity-log.index') }}?subject_type='+this.value"
				class="select select-sm w-auto">
				<option value="">All Models</option>
				<option value="App\Models\User" {{ request('subject_type') === 'App\Models\User' ? 'selected' : '' }}>User</option>
				<option value="App\Models\Role" {{ request('subject_type') === 'App\Models\Role' ? 'selected' : '' }}>Role</option>
				<option value="App\Models\Permission" {{ request('subject_type') === 'App\Models\Permission' ? 'selected' : '' }}>
					Permission</option>
			</select>

			<select name="event" onchange="window.location.href='{{ route('activity-log.index') }}?event='+this.value"
				class="select select-sm w-auto">
				<option value="">All Events</option>
				<option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
				<option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
				<option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
			</select>
		</div>

		<div class="overflow-x-auto">
			<table class="table">
				<thead>
					<tr>
						<th>Description</th>
						<th>Causer</th>
						<th>Event</th>
						<th>Date</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@forelse ($activities as $activity)
						<tr>
							<td>{{ $activity->description }}</td>
							<td>{{ $activity->causer?->name ?? 'System' }}</td>
							<td>
								<span
									class="badge badge-sm {{ match ($activity->event) {
									    'created' => 'badge-success',
									    'updated' => 'badge-warning',
									    'deleted' => 'badge-error',
									    default => 'badge-neutral',
									} }}">
									{{ $activity->event }}
								</span>
							</td>
							<td>{{ $activity->created_at->diffForHumans() }}</td>
							<td>
								@if ($activity->properties->isNotEmpty())
									<button type="button" class="btn btn-sm btn-soft btn-primary" data-overlay="#detail-{{ $activity->id }}">
										Detail
									</button>

									<div id="detail-{{ $activity->id }}" class="overlay modal overlay-open:opacity-100 hidden" role="dialog"
										tabindex="-1">
										<div class="modal-dialog overlay-open:opacity-100">
											<div class="modal-content">
												<div class="modal-header">
													<h3 class="modal-title">Activity Detail</h3>
													<button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close"
														data-overlay="#detail-{{ $activity->id }}">
														<span class="icon-[tabler--x] size-5"></span>
													</button>
												</div>
												<div class="modal-body">
													<p class="text-base-content/70 mb-3">{{ $activity->description }}</p>

													<div class="flex flex-col gap-2">
														@php
															$old = $activity->properties['old'] ?? [];
															$new = $activity->properties['attributes'] ?? [];
															$fields = collect($new)
															    ->keys()
															    ->merge(collect($old)->keys())
															    ->unique();
														@endphp

														@forelse ($fields as $field)
															<div class="rounded-box border border-base-300 p-3">
																<div class="text-sm font-medium text-base-content mb-2">{{ $field }}</div>
																<div class="flex items-center gap-2 flex-wrap">
																	@if (array_key_exists($field, $old))
																		<span class="badge badge-error badge-soft">
																			{{ is_array($old[$field] ?? null) ? implode(', ', $old[$field]) : $old[$field] ?? 'null' }}
																		</span>
																		<span class="icon-[tabler--arrow-right] size-4 text-base-content/40"></span>
																	@endif
																	<span class="badge badge-success badge-soft">
																		{{ is_array($new[$field] ?? null) ? implode(', ', $new[$field]) : $new[$field] ?? 'null' }}
																	</span>
																</div>
															</div>
														@empty
															<p class="text-sm text-base-content/50">No field details recorded.</p>
														@endforelse
													</div>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-soft btn-secondary" data-overlay="#detail-{{ $activity->id }}">
														Close
													</button>
												</div>
											</div>
										</div>
									</div>
								@endif
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="text-center text-base-content/50 py-6">No activity found.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="mt-4">
			{{ $activities->links('vendor.pagination.flyonui') }}
		</div>
	</div>
@endsection
