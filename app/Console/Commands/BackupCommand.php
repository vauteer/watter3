<?php

namespace App\Console\Commands;

use App\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (!Backup::isDirty()) {
            $message = 'No backup made. No changes since the last backup.';
            Log::info($message);
            $this->info($message);

            return;
        }

        $this->info('Create backup ...');

        $time = -hrtime(true);

        Backup::create();

        $seconds = number_format(($time + hrtime(true)) / 1e+9, 2);

        $info = "Backup created in in {$seconds} seconds";
        $this->info($info);
        Log::info("app:backup {$info}");
    }
}
