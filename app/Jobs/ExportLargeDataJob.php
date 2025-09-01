<?php

namespace App\Jobs;

use App\Models\User;
use App\Exports\LargeDiamondDataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExportLargeDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $queue = 'bigdata';

    public $timeout = 1800;
    public $tries = 2;

    protected $user;
    protected $filters;

    public function __construct(User $user, array $filters = [])
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    public function handle()
    {
        $filename = 'diamond_export_' . date('Y_m_d_H_i_s') . '.xlsx';
        $filePath = 'exports/' . $filename;

        Excel::store(new LargeDiamondDataExport($this->filters), $filePath, 'public');

        // Send email notification with download link
        $downloadUrl = asset('storage/' . $filePath);

    }
}