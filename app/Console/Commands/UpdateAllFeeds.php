<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFeedItems;
use App\Models\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateAllFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:update-all';

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
        Log::info('Starting feed:update-all command');

        // Получаем все активные фиды
        $feeds = Feed::where('is_active', true)->get();

        // Если фидов нет, выводим сообщение
        if ($feeds->isEmpty()) {
            $this->warn('No active feeds found.');
            Log::info('No active feeds found.');
            return;
        }

        // Создаем массив задач
        $jobs = $feeds->map(fn($feed) => new ProcessFeedItems($feed))->toArray();

        try {
            // Отправляем задачи в пакет
            $batch = Bus::batch($jobs)
                ->then(function () {
                    Log::info('All feeds processed successfully.');
                })
                ->catch(function (Throwable $e) {
                    Log::error('Error processing feeds: ' . $e->getMessage());
                })
                ->allowFailures() // Разрешаем продолжать обработку, даже если одна задача завершится неудачно
                ->dispatch();

            $this->info("Feed processing started for all feeds.");
            Log::info("Feed processing started for all feeds. Batch ID: " . $batch->id);
        } catch (Throwable $e) {
            $this->error('Failed to start feed processing: ' . $e->getMessage());
            Log::error('Failed to start feed processing: ' . $e->getMessage());
        }
    }
}
