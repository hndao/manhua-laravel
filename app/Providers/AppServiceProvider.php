<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enable query logging only in local environment
        if (config('app.env') === 'local' && config('database.log_queries', false)) {
            DB::listen(function ($query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;

                // Replace bindings in SQL for better readability
                foreach ($bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'{$binding}'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }

                // Log query with execution time
                $logMessage = sprintf(
                    "[%s ms] %s",
                    number_format($time, 2),
                    $sql
                );

                // Log to dedicated query log file
                Log::channel('query')->info($logMessage);

                // Also log slow queries (> 100ms) to a separate file
                if ($time > 100) {
                    Log::channel('slow_query')->warning($logMessage, [
                        'execution_time' => $time,
                        'bindings' => $bindings,
                    ]);
                }
            });
        }
    }
}
