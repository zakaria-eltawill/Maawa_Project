<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;

class GenerateOccupancyReportJob extends GenerateReportJob
{
    protected function generateFile(): string
    {
        $filters = $this->export->filters;
        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);

        // Query occupancy data grouped by property
        $properties = Property::with(['owner'])
            ->get()
            ->map(function ($property) use ($from, $to) {
                $bookings = Booking::where('property_id', $property->id)
                    ->whereBetween('check_in', [$from, $to])
                    ->whereIn('status', ['CONFIRMED', 'COMPLETED'])
                    ->get();

                $totalNights = $bookings->sum(function ($booking) {
                    return $booking->check_in->diffInDays($booking->check_out);
                });

                $totalDays = $from->diffInDays($to) + 1;
                $occupancyRate = $totalDays > 0 ? ($totalNights / $totalDays) * 100 : 0;

                return [
                    'property' => $property,
                    'total_bookings' => $bookings->count(),
                    'total_nights' => $totalNights,
                    'occupancy_rate' => round($occupancyRate, 2),
                ];
            })
            ->filter(fn($item) => $item['total_bookings'] > 0)
            ->sortByDesc('total_nights');

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
            'Total Bookings',
            'Total Nights Booked',
            'Occupancy Rate (%)',
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
                $item['total_bookings'],
                $item['total_nights'],
                $item['occupancy_rate'],
                "{$from->format('Y-m-d')} to {$to->format('Y-m-d')}",
            ]);
        }

        fclose($file);

        return $filePath;
    }
}

