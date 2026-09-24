<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkMissedFollowUps extends Command
{
    protected $signature = 'app:mark-missed-followups';
    protected $description = 'Mark pending follow-ups that are past their scheduled date as missed';

    public function handle(): int
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $updated = FollowUp::where('status', 'pending')
            ->whereDate('follow_up_date', '<=', $yesterday)
            ->update([
                'status' => 'missed',
                'updated_at' => Carbon::now(),
            ]);

        $this->info("Marked {$updated} follow-up(s) as missed.");

        return Command::SUCCESS;
    }
}
