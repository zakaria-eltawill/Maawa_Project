<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportReportRequest;
use App\Jobs\GenerateBookingsReportJob;
use App\Jobs\GenerateOccupancyReportJob;
use App\Jobs\GenerateRevenueReportJob;
use App\Models\Export;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index()
    {
        $exports = Export::where('created_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.index', compact('exports'));
    }

    public function export(ExportReportRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Create export record
        $export = Export::create([
            'type' => $request->type,
            'filters' => [
                'format' => $request->format,
                'from' => $request->from,
                'to' => $request->to,
            ],
            'status' => 'QUEUED',
            'created_by' => $user->id,
        ]);

        // Dispatch appropriate job based on report type
        match ($request->type) {
            'bookings' => GenerateBookingsReportJob::dispatch($export),
            'occupancy' => GenerateOccupancyReportJob::dispatch($export),
            'revenue' => GenerateRevenueReportJob::dispatch($export),
        };

        return back()->with('success', __('admin.export_queued', [], 'en'));
    }

    public function download(Export $export): BinaryFileResponse
    {

        // Check if user owns this export or is admin
        if ($export->created_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Check if export is ready
        if ($export->status !== 'READY' || !$export->file_path) {
            abort(404, 'Export not ready or file not found');
        }

        // Check if file exists
        $fullPath = storage_path("app/{$export->file_path}");
        if (!file_exists($fullPath)) {
            abort(404, 'Export file not found');
        }

        $format = $export->filters['format'] ?? 'csv';
        $extension = $format === 'pdf' ? 'pdf' : 'csv';
        $filename = "{$export->type}_report_{$export->created_at->format('Y-m-d_His')}.{$extension}";

        return response()->download($fullPath, $filename, [
            'Content-Type' => $format === 'pdf' ? 'application/pdf' : 'text/csv',
        ]);
    }
}
