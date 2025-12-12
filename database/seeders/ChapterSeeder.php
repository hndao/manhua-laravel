<?php

namespace Database\Seeders;

use App\Models\Chapter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = base_path('sampleData/chapters.csv');

        if (!File::exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $file = fopen($csvFile, 'r');
        fgetcsv($file); // Skip header row

        $count = 0;
        $skipped = 0;
        $chaptersByComic = [];

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0]) || empty($row[1])) {
                continue; // Skip empty rows
            }

            $title = !empty($row[2]) ? $row[2] : 'Chapter ' . $row[3];
            $chapterNumber = $row[3] ?? 1;
            $comicId = $row[1];

            // Track chapter numbers per comic to detect duplicates
            if (!isset($chaptersByComic[$comicId])) {
                $chaptersByComic[$comicId] = [];
            }

            // If this chapter number already exists for this comic, increment it
            while (in_array($chapterNumber, $chaptersByComic[$comicId])) {
                $chapterNumber++;
            }
            $chaptersByComic[$comicId][] = $chapterNumber;

            try {
                Chapter::updateOrCreate(
                    ['id' => $row[0]],
                    [
                        'comic_id' => $comicId,
                        'title' => $title,
                        'slug' => Str::slug($title . '-' . $row[0]),
                        'chapter_number' => $chapterNumber,
                        'volume_number' => null,
                        'total_pages' => 0,
                        'views' => 0,
                        'published_at' => now(),
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $skipped++;
                $this->command->warn("Skipped chapter ID {$row[0]}: " . $e->getMessage());
            }
        }

        fclose($file);

        $this->command->info("Chapters seeded successfully! Imported: {$count}, Skipped: {$skipped}");
    }
}
