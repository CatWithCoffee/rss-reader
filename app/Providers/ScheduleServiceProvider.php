<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Проверяем, что мы в консольном окружении
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $this->schedule($schedule);
                \Log::info('ScheduleServiceProvider booted and schedule method called');
            });
        }
    }

    protected function schedule(Schedule $schedule)
    {
        \Log::info('Schedule started');
        $schedule->command('feed:update-all')
            ->everyMinute()
            ->onFailure(function () {
                \Log::error('Command feed:update-all failed');
            });
    }

}
