<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use Faker\Factory as Faker;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            Author::create([
                'fullname' => $faker->name,
                'about' => $faker->paragraph,
                'image' => $faker->optional()->imageUrl(200, 200, 'people'),
            ]);
        }
    }
}
