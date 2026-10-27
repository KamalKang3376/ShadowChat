<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Participants;
use App\Models\Report;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRooms = Room::count();

        $activeRooms = Room::where('status', 'active')->count();

        $totalParticipants = Participants::count();

        $pendingReports = Report::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalRooms',
            'activeRooms',
            'totalParticipants',
            'pendingReports'
        ));
    }
}