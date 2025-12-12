<?php

namespace Database\Seeders;

use App\Models\Comic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ComicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed comics
        $comicsFile = base_path('sampleData/comics.csv');

        if (!File::exists($comicsFile)) {
            $this->command->error('CSV file not found: ' . $comicsFile);
            return;
        }

        $file = fopen($comicsFile, 'r');
        fgetcsv($file); // Skip header row

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0]) || empty($row[1])) {
                continue; // Skip empty rows
            }

            Comic::updateOrCreate(
                ['id' => $row[0]],
                [
                    'title' => $row[1],
                    'slug' => Str::slug($row[1]),
                    'description' => $row[2] ?? null,
                    'cover_image' => $row[3] ?? null,
                    'status' => 'ongoing',
                    'type' => 'manhua',
                ]
            );
        }

        fclose($file);
        $this->command->info('Comics seeded successfully!');

        // Assign random genres to comics
        $this->assignRandomGenres();

        // Seed comic-author relationships
        $comicAuthorFile = base_path('sampleData/comic_author.csv');

        if (!File::exists($comicAuthorFile)) {
            $this->command->warn('Comic-Author CSV file not found: ' . $comicAuthorFile);
            return;
        }

        $file = fopen($comicAuthorFile, 'r');
        fgetcsv($file); // Skip header row

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                continue; // Skip empty rows
            }

            // Insert into pivot table
            DB::table('author_comic')->updateOrInsert(
                [
                    'comic_id' => $row[1],
                    'author_id' => $row[2],
                ],
                [
                    'role' => 'both',
                    'created_at' => $row[3] ?? now(),
                    'updated_at' => now(),
                ]
            );
        }

        fclose($file);
        $this->command->info('Comic-Author relationships seeded successfully!');
    }

    /**
     * Assign random genres to comics
     */
    private function assignRandomGenres(): void
    {
        $comics = Comic::all();
        $genres = \App\Models\Genre::all();

        if ($genres->isEmpty()) {
            $this->command->warn('No genres found. Please run GenreSeeder first.');
            return;
        }

        foreach ($comics as $comic) {
            // Randomly assign 1-5 genres to each comic
            $randomGenreCount = rand(1, 5);
            $randomGenres = $genres->random(min($randomGenreCount, $genres->count()));

            // Sync genres (this will remove old associations and add new ones)
            $comic->genres()->sync($randomGenres->pluck('id')->toArray());
        }

        $this->command->info('Random genres assigned to comics successfully!');
    }
}
