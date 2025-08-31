<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessBigDataQueue extends Command
{
    protected $signature = 'queue:bigdata {--timeout=600}';
    protected $description = 'Process big data upload queue with optimized settings';

    public function handle()
    {
        $this->info('Starting big data queue worker...');

        $timeout = $this->option('timeout');

        $this->call('queue:work', [
            '--queue' => 'bigdata',
            '--timeout' => $timeout,
            '--memory' => 512,
            '--tries' => 3,
            '--sleep' => 3,
            '--max-jobs' => 100,
        ]);
    }
}