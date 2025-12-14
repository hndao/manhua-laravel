<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding pages from CSV files...');

        // Delete existing pages
        Page::truncate();

        // Import from page_1.csv
        $this->importFromCsv('sampleData/page_1.csv');

        // Import from page_2.csv
        $this->importFromCsv('sampleData/page_2.csv');

        $this->command->info('Pages seeded successfully!');
    }

    /**
     * Import pages from a CSV file
     */
    private function importFromCsv(string $filePath): void
    {
        if (!file_exists($filePath)) {
            $this->command->warn("File not found: {$filePath}");
            return;
        }

        $this->command->info("Importing from {$filePath}...");

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Skip header row

        $pages = [];
        $count = 0;
        $batchSize = 500;

        while (($row = fgetcsv($file)) !== false) {
            // Check if chapter exists
            $chapterExists = Chapter::where('id', $row[1])->exists();

            if (!$chapterExists) {
                continue; // Skip if chapter doesn't exist
            }

            $pages[] = [
                'id' => $row[0],
                'chapter_id' => $row[1],
                'page_number' => $row[2],
                'image_url' => $row[3],
                'width' => null,
                'height' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            // Insert in batches
            if (count($pages) >= $batchSize) {
                DB::table('pages')->insert($pages);
                $pages = [];
                $this->command->info("Inserted {$count} pages...");
            }
        }

        // Insert remaining pages
        if (!empty($pages)) {
            DB::table('pages')->insert($pages);
        }

        fclose($file);

        $this->command->info("Imported {$count} pages from {$filePath}");
    }
}
