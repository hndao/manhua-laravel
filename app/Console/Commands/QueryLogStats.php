<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class QueryLogStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'query:stats
                            {--slow : Show only slow queries}
                            {--limit=20 : Number of queries to display}
                            {--today : Show only today\'s queries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display database query statistics and performance metrics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $showSlow = $this->option('slow');
        $limit = (int) $this->option('limit');
        $today = $this->option('today');

        // Determine which log file to read
        $logPath = $showSlow
            ? storage_path('logs/slow-query.log')
            : storage_path('logs/query.log');

        // If today option is set, use today's log file
        if ($today) {
            $date = now()->format('Y-m-d');
            $logPath = $showSlow
                ? storage_path("logs/slow-query-{$date}.log")
                : storage_path("logs/query-{$date}.log");
        }

        if (!File::exists($logPath)) {
            $this->error("Log file not found: {$logPath}");
            $this->info('Make sure DB_LOG_QUERIES=true is set in your .env file.');
            return 1;
        }

        $this->info('Reading query logs from: ' . basename($logPath));
        $this->newLine();

        // Read and parse log file
        $content = File::get($logPath);
        $lines = explode("\n", $content);

        $queries = [];
        $totalTime = 0;
        $queryCount = 0;

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Parse log line: [2024-01-26 08:00:00] local.INFO: [123.45 ms] SELECT * FROM ...
            if (preg_match('/\[(\d+\.\d+) ms\] (.+)/', $line, $matches)) {
                $time = (float) $matches[1];
                $sql = $matches[2];

                $queries[] = [
                    'time' => $time,
                    'sql' => $sql,
                ];

                $totalTime += $time;
                $queryCount++;
            }
        }

        if ($queryCount === 0) {
            $this->warn('No queries found in the log file.');
            return 0;
        }

        // Sort queries by execution time (slowest first)
        usort($queries, fn($a, $b) => $b['time'] <=> $a['time']);

        // Display statistics
        $this->displayStats($queries, $totalTime, $queryCount, $limit);

        return 0;
    }

    /**
     * Display query statistics
     */
    private function displayStats(array $queries, float $totalTime, int $queryCount, int $limit): void
    {
        // Summary statistics
        $this->info('📊 Query Performance Summary');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Queries', number_format($queryCount)],
                ['Total Time', number_format($totalTime, 2) . ' ms'],
                ['Average Time', number_format($totalTime / $queryCount, 2) . ' ms'],
                ['Slowest Query', number_format($queries[0]['time'], 2) . ' ms'],
                ['Fastest Query', number_format($queries[count($queries) - 1]['time'], 2) . ' ms'],
            ]
        );

        $this->newLine();

        // Display slowest queries
        $this->info("🐌 Top {$limit} Slowest Queries");
        $this->newLine();

        $displayQueries = array_slice($queries, 0, $limit);

        foreach ($displayQueries as $index => $query) {
            $number = $index + 1;
            $time = number_format($query['time'], 2);
            $sql = $this->truncateQuery($query['sql'], 100);

            // Color code based on execution time
            $color = $this->getColorForTime($query['time']);

            $this->line("<fg={$color}>{$number}. [{$time} ms]</> {$sql}");
        }

        $this->newLine();
    }

    /**
     * Truncate long SQL queries
     */
    private function truncateQuery(string $sql, int $maxLength): string
    {
        if (strlen($sql) <= $maxLength) {
            return $sql;
        }

        return substr($sql, 0, $maxLength) . '...';
    }

    /**
     * Get color based on execution time
     */
    private function getColorForTime(float $time): string
    {
        if ($time >= 1000) {
            return 'red';
        } elseif ($time >= 500) {
            return 'yellow';
        } elseif ($time >= 100) {
            return 'cyan';
        }

        return 'green';
    }
}
