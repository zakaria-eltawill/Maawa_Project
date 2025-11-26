<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;

class GenerateRevenueReportJob extends GenerateReportJob
{
    protected function generateFile(): string
    {
        $filters = $this->export->filters;
        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);

        // Query revenue data grouped by property
        $properties = Property::with(['owner'])
            ->get()
            ->map(function ($property) use ($from, $to) {
                $bookings = Booking::where('property_id', $property->id)
                    ->whereBetween('check_in', [$from, $to])
                    ->whereIn('status', ['CONFIRMED', 'COMPLETED'])
                    ->get();

                $totalRevenue = $bookings->sum('total');
                $bookingCount = $bookings->count();
                $averageBooking = $bookingCount > 0 ? $totalRevenue / $bookingCount : 0;

                return [
                    'property' => $property,
                    'total_revenue' => $totalRevenue,
                    'booking_count' => $bookingCount,
                    'average_booking' => $averageBooking,
                ];
            })
            ->filter(fn($item) => $item['total_revenue'] > 0)
            ->sortByDesc('total_revenue');

        return $this->generateCsv($properties, $from, $to);
    }

    private function generateCsv($properties, $from, $to): string
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
            'Property Title',
            'City',
            'Owner Name',
            'Owner Email',
            'Total Revenue',
            'Number of Bookings',
            'Average Booking Value',
            'Date Range',
        ]);

        // Data rows
        foreach ($properties as $item) {
            $property = $item['property'];
            fputcsv($file, [
                $property->title,
                $property->city,
                $property->owner->name ?? 'N/A',
                $property->owner->email ?? 'N/A',
                number_format($item['total_revenue'], 2),
                $item['booking_count'],
                number_format($item['average_booking'], 2),
                "{$from->format('Y-m-d')} to {$to->format('Y-m-d')}",
            ]);
        }

        // Summary row
        $totalRevenue = $properties->sum(fn($item) => $item['total_revenue']);
        $totalBookings = $properties->sum(fn($item) => $item['booking_count']);
        $overallAverage = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;

        fputcsv($file, []); // Empty row
        fputcsv($file, ['TOTAL', '', '', '', number_format($totalRevenue, 2), $totalBookings, number_format($overallAverage, 2), '']);

        fclose($file);

        return $filePath;
    }
}

