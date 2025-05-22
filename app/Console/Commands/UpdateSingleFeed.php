<?php

namespace App\Console\Commands;

use App\Jobs\ProcessArticles;
use App\Models\Feed;
use Illuminate\Console\Command;
use Log;

class UpdateSingleFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:update {feed_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts saving articles from a single feed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Получаем ID фида из аргумента
        $feedId = $this->argument('feed_id');

        // Ищем фид в базе данных
        $feed = Feed::find($feedId);

        if (!$feed) {
            $this->error("Feed with ID {$feedId} not found.");
            return;
        }

        // Проверяем, активен ли фид
        if (!$feed->is_active) {
            $this->warn("Feed with ID {$feedId} is inactive.");
            return;
        }

        // Запускаем Job для обработки фида
        ProcessArticles::dispatch($feed);

        $this->info("Feed processing started for feed ID {$feedId}.");
        Log::info("Feed processing started for feed ID {$feedId}.");
    }
}
