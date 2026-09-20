<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with(['causer', 'subject'])
            ->when($request->subject_type, fn ($q) => $q->where('subject_type', $request->subject_type))
            ->when($request->event, fn ($q) => $q->where('event', $request->event))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('activity-log.index', compact('activities'));
    }
}