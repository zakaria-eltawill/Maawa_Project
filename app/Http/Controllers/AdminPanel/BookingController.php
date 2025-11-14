<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.bookings.index');
    }

    public function show(string $id)
    {
        return view('admin.bookings.show', compact('id'));
    }
}
