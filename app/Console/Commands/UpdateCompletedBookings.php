<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCompletedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-completed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update bookings with passed check-out dates from CONFIRMED/ACCEPTED to COMPLETED status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update completed bookings...');

        // Get today's date (start of day)
        $today = now()->startOfDay();

        // Find all bookings with status CONFIRMED or ACCEPTED where check_out has passed
        $bookings = Booking::whereIn('status', ['CONFIRMED', 'ACCEPTED'])
            ->whereDate('check_out', '<', $today)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings found that need to be updated.');
            return Command::SUCCESS;
        }

        $count = 0;

        // Update each booking
        foreach ($bookings as $booking) {
            $booking->update([
                'status' => 'COMPLETED',
            ]);
            $count++;
        }

        $this->info("Successfully updated {$count} booking(s) to COMPLETED status.");

        return Command::SUCCESS;
    }
}
