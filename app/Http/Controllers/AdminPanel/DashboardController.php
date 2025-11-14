<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Proposal;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $stats = [
            'pending_proposals' => Proposal::where('status', 'PENDING')->count(),
            'todays_bookings' => Booking::whereDate('check_in', today())->count(),
            'pending_payments' => Booking::where('status', 'CONFIRMED')
                ->whereNotNull('payment_due_at')
                ->where('payment_due_at', '>', now())
                ->count(),
            'total_properties' => Property::count(),
            'total_bookings' => Booking::count(),
            'total_users' => User::count(),
            'total_owners' => User::where('role', 'owner')->count(),
            'total_tenants' => User::where('role', 'tenant')->count(),
            'approved_proposals' => Proposal::where('status', 'APPROVED')->count(),
            'rejected_proposals' => Proposal::where('status', 'REJECTED')->count(),
            'confirmed_bookings' => Booking::where('status', 'CONFIRMED')->count(),
            'completed_bookings' => Booking::where('status', 'COMPLETED')->count(),
        ];

        // Recent Activities
        $recentProposals = Proposal::with(['owner', 'property'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentBookings = Booking::with(['tenant', 'property'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Today's activities
        $todayProposals = Proposal::whereDate('created_at', today())->count();
        $todayBookings = Booking::whereDate('created_at', today())->count();

        // This week's statistics
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $weekStats = [
            'proposals' => Proposal::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'bookings' => Booking::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'users' => User::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
        ];

        return view('admin.dashboard.index', compact(
            'stats',
            'recentProposals',
            'recentBookings',
            'todayProposals',
            'todayBookings',
            'weekStats'
        ));
    }
}
