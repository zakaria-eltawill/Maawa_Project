<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function export(Request $request)
    {
        // Placeholder dispatch job here later
        return back()->with('status', __('admin.export_queued'));
    }

    public function download(string $export)
    {
        // Placeholder for signed download link
        abort(404);
    }
}
