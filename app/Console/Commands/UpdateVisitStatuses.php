<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VisitReservation;
use Carbon\Carbon;

class UpdateVisitStatuses extends Command
{
    protected $signature = 'visits:update-statuses';
    protected $description = 'Update visit statuses to done or cancelled after their end time';

    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $time = $now->format('H:i');

        // ✅ إذا حضر المستخدم: status = done
        VisitReservation::where(function ($q) use ($today, $time) {
                $q->where('visit_date', '<', $today)
                  ->orWhere(function ($q2) use ($today, $time) {
                      $q2->where('visit_date', $today)
                          ->where('end_time', '<=', $time);
                  });
            })
            ->where('status', 'checked_in')
            ->update(['status' => 'done']);

        // ❌ إذا لم يحضر: status = cancelled
        VisitReservation::where(function ($q) use ($today, $time) {
                $q->where('visit_date', '<', $today)
                  ->orWhere(function ($q2) use ($today, $time) {
                      $q2->where('visit_date', $today)
                          ->where('end_time', '<=', $time);
                  });
            })
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $this->info('Past visits updated successfully.');
    }
}
