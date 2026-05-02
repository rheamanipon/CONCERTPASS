<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function metrics()
    {
        return response()->json([
            'users' => User::count(),
            'concerts' => Concert::count(),
            'bookings' => Booking::count(),
            'tickets_sold' => Ticket::count(),
            'revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
        ]);
    }

    public function analytics()
    {
        $monthly = Payment::selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as month")
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('SUM(amount) as revenue')
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $channels = Payment::select('payment_method', DB::raw('SUM(amount) as revenue'))
            ->where('status', 'paid')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        return response()->json([
            'monthly' => $monthly,
            'channels' => $channels,
        ]);
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user:id,name,email')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($logs);
    }
}
