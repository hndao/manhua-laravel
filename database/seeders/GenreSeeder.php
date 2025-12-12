<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, seed from CSV if exists
        $csvFile = base_path('sampleData/genres.csv');

        if (File::exists($csvFile)) {
            $file = fopen($csvFile, 'r');
            fgetcsv($file); // Skip header row

            while (($row = fgetcsv($file)) !== false) {
                if (empty($row[0]) || empty($row[1])) {
                    continue; // Skip empty rows
                }

                Genre::updateOrCreate(
                    ['id' => $row[0]],
                    [
                        'name' => $row[1],
                        'slug' => Str::slug($row[1]),
                        'description' => null,
                    ]
                );
            }

            fclose($file);
        }

        // Add additional genres with specific IDs to avoid conflicts
        $additionalGenres = [
            ['id' => 1, 'name' => 'Action'],
            ['id' => 2, 'name' => 'Adventure'],
            ['id' => 3, 'name' => 'Comedy'],
            ['id' => 4, 'name' => 'Drama'],
            ['id' => 5, 'name' => 'Fantasy'],
            ['id' => 6, 'name' => 'Horror'],
            ['id' => 7, 'name' => 'Mystery'],
            ['id' => 8, 'name' => 'Romance'],
            ['id' => 9, 'name' => 'Sci-Fi'],
            ['id' => 10, 'name' => 'Slice of Life'],
            ['id' => 11, 'name' => 'Supernatural'],
            ['id' => 12, 'name' => 'Thriller'],
            ['id' => 13, 'name' => 'Martial Arts'],
            ['id' => 14, 'name' => 'Historical'],
            ['id' => 15, 'name' => 'School Life'],
            ['id' => 16, 'name' => 'Psychological'],
            ['id' => 17, 'name' => 'Tragedy'],
            ['id' => 18, 'name' => 'Seinen'],
            ['id' => 19, 'name' => 'Shounen'],
            ['id' => 20, 'name' => 'Shoujo'],
            ['id' => 21, 'name' => 'Josei'],
            ['id' => 22, 'name' => 'Isekai'],
            ['id' => 23, 'name' => 'Cultivation'],
            ['id' => 24, 'name' => 'Xuanhuan'],
            ['id' => 27, 'name' => 'Wuxia'],
            ['id' => 28, 'name' => 'Xianxia'],
            ['id' => 29, 'name' => 'Mecha'],
            ['id' => 30, 'name' => 'Sports'],
            ['id' => 31, 'name' => 'Music'],
            ['id' => 32, 'name' => 'Harem'],
        ];

        foreach ($additionalGenres as $genre) {
            Genre::updateOrCreate(
                ['id' => $genre['id']],
                [
                    'name' => $genre['name'],
                    'slug' => Str::slug($genre['name']),
                    'description' => null,
                ]
            );
        }

        $this->command->info('Genres seeded successfully!');
    }
}
