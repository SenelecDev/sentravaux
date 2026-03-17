<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('causer')) {
            $query->whereHasMorph('causer', ['App\Models\User'], function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->causer . '%')
                  ->orWhere('matricule', 'like', '%' . $request->causer . '%');
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $activities = $query->paginate(30)->withQueryString();

        $logNames = Activity::distinct()->pluck('log_name')->filter()->sort()->values();

        return view('admin.activity-log.index', compact('activities', 'logNames'));
    }
}
