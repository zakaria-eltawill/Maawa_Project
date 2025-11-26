<?php

namespace App\Jobs;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateBookingsReportJob extends GenerateReportJob
{
    protected function generateFile(): string
    {
        $filters = $this->export->filters;
        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);

        // Query bookings data
        $bookings = Booking::with(['property', 'tenant'])
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->generateCsv($bookings, $from, $to);
    }

    private function generateCsv($bookings, $from, $to): string
    {
        $filePath = $this->getStoragePath();
        $fullPath = storage_path("app/{$filePath}");

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($fullPath, 'w');

        // BOM for UTF-8 (Excel compatibility)
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($file, [
            'Booking ID',
            'Property Title',
            'City',
            'Tenant Name',
            'Tenant Email',
            'Tenant Phone',
            'Check-in Date',
            'Check-out Date',
            'Nights',
            'Guests',
            'Total Amount',
            'Status',
            'Payment Status',
            'Created Date',
        ]);

        // Data rows
        foreach ($bookings as $booking) {
            $nights = $booking->check_in->diffInDays($booking->check_out);
            $paymentStatus = $booking->is_paid ? 'Paid' : 'Unpaid';

            fputcsv($file, [
                $booking->id,
                $booking->property->title ?? 'N/A',
                $booking->property->city ?? 'N/A',
                $booking->tenant->name ?? 'N/A',
                $booking->tenant->email ?? 'N/A',
                $booking->tenant->phone_number ?? 'N/A',
                $booking->check_in->format('Y-m-d'),
                $booking->check_out->format('Y-m-d'),
                $nights,
                $booking->guests,
                number_format($booking->total, 2),
                $booking->status,
                $paymentStatus,
                $booking->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return $filePath;
    }
}

