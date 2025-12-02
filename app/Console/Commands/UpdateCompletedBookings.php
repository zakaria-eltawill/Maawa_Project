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

        // Get today's date (start of day) - using Carbon for reliable date comparison
        $today = now()->startOfDay();
        $this->line("Today's date (start of day): {$today->toDateString()}");

        // Find all bookings with status CONFIRMED or ACCEPTED
        $allEligibleBookings = Booking::whereIn('status', ['CONFIRMED', 'ACCEPTED'])->get();
        
        $this->line("Found {$allEligibleBookings->count()} booking(s) with CONFIRMED or ACCEPTED status.");

        // Filter bookings where check_out has passed (using Carbon comparison for reliability)
        $bookings = $allEligibleBookings->filter(function ($booking) use ($today) {
            $checkOutDate = \Carbon\Carbon::parse($booking->check_out)->startOfDay();
            $hasPassed = $checkOutDate->lt($today); // less than today
            
            if ($this->option('verbose')) {
                $this->line("Booking ID: {$booking->id}, Status: {$booking->status}, Check-out: {$checkOutDate->toDateString()}, Has passed: " . ($hasPassed ? 'YES' : 'NO'));
            }
            
            return $hasPassed;
        });

        if ($bookings->isEmpty()) {
            $this->info('No bookings found that need to be updated.');
            
            // Show some debug info if verbose
            if ($this->option('verbose') && $allEligibleBookings->isNotEmpty()) {
                $this->line("\nEligible bookings (not yet past check-out):");
                foreach ($allEligibleBookings->take(5) as $booking) {
                    $checkOutDate = \Carbon\Carbon::parse($booking->check_out)->startOfDay();
                    $this->line("  - Booking #{$booking->id}: Status={$booking->status}, Check-out={$checkOutDate->toDateString()}");
                }
            }
            
            return Command::SUCCESS;
        }

        $count = 0;

        // Update each booking
        foreach ($bookings as $booking) {
            $oldStatus = $booking->status;
            $booking->update([
                'status' => 'COMPLETED',
            ]);
            $count++;
            
            if ($this->option('verbose')) {
                $this->line("Updated Booking #{$booking->id} from {$oldStatus} to COMPLETED (Check-out: {$booking->check_out})");
            }
        }

        $this->info("Successfully updated {$count} booking(s) to COMPLETED status.");

        return Command::SUCCESS;
    }
}
