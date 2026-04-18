<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->has('log_name') && $request->log_name) {
            $query->where('log_name', $request->log_name);
        }
        
        $logs = $query->paginate(20);
        
        return view('admin.activity_logs.index', compact('logs'));
    }
}
