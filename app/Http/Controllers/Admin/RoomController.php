<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(){
        $rooms = Room::withCount('participants')
            ->latest()
            ->get();

            return view('admin.rooms.index',compact('rooms'));
    }
}
