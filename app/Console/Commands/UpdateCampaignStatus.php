<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateCampaignStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $now = Carbon::now();

    /**
     * مكتملة (وصلت الهدف)
     */
    DB::table('campaigns')
        ->whereNotNull('target_amount')
        ->whereColumn('collected_amount', '>=', 'target_amount')
        ->update(['status' => 'مكتملة']);

    /**
     * منتهية
     */
    DB::table('campaigns')
        ->where('status', '!=', 'ملغاة')
        ->whereRaw("CONCAT(end_date,' ',end_time) < ?", [$now])
        ->update(['status' => 'منتهية']);

    /**
     * نشطة
     */
    DB::table('campaigns')
        ->where('status', '!=', 'ملغاة')
        ->whereRaw("CONCAT(start_date,' ',start_time) <= ?", [$now])
        ->whereRaw("CONCAT(end_date,' ',end_time) >= ?", [$now])
        ->update(['status' => 'نشطة']);

    /**
     * متوقفة (لم تبدأ بعد)
     */
    DB::table('campaigns')
        ->where('status', '!=', 'ملغاة')
        ->whereRaw("CONCAT(start_date,' ',start_time) > ?", [$now])
        ->update(['status' => 'متوقفة']);

    $this->info('Campaign statuses updated successfully.');
}
}
