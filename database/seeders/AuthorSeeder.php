<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = base_path('sampleData/authors.csv');

        if (!File::exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $file = fopen($csvFile, 'r');
        fgetcsv($file); // Skip header row

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) {
                continue; // Skip empty rows
            }

            $name = !empty($row[1]) ? $row[1] : 'Author ' . $row[0];
            $slug = Str::slug($name);

            // Make slug unique by appending ID if needed
            $existingAuthor = Author::where('slug', $slug)->where('id', '!=', $row[0])->first();
            if ($existingAuthor) {
                $slug = $slug . '-' . $row[0];
            }

            Author::updateOrCreate(
                ['id' => $row[0]],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'bio' => null,
                    'avatar' => null,
                    'website' => null,
                ]
            );
        }

        fclose($file);

        $this->command->info('Authors seeded successfully!');
    }
}
