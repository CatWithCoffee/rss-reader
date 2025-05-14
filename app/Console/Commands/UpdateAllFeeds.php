<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFeedItems;
use Bus;
use Illuminate\Console\Command;
use App\Models\Feed;
use Log;

class UpdateAllFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-all-feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts saving items from all feeds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeds = Feed::where('is_active', true)->get();

        $batch = Bus::batch(
            $feeds->map(fn($feed) => new ProcessFeedItems($feed)))
            ->then(fn() => Log::info('All feeds processed'))
            ->allowFailures()
            ->dispatch();
        
        $this->info("Feed processing started for all feeds.");
        Log::info("Feed processing started for all feeds.");
    }
}
