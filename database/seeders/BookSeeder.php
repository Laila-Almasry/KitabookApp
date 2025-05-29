<?php

   namespace Database\Seeders;

   use Illuminate\Database\Seeder;
   use App\Models\Book;
   use App\Models\Category;
   use Faker\Factory as Faker;

   class BookSeeder extends Seeder
   {
       /**
        * Run the database seeds.
        *
        * @return void
        */
       public function run()
       {
           $faker = Faker::create();

           // Get all categories
           $categories = Category::all();

           for ($i = 0; $i < 10; $i++) {
               Book::create([
                   'barcode' => $faker->unique()->ean13,
                   'title' => $faker->sentence(3),
                   'preview' => $faker->paragraph,
                   'cover_image' => $faker->imageUrl(640, 480, 'books'),
                   'author_id' => $faker->numberBetween(1, 10), // Assuming you have authors with IDs from 1 to 10
                   'price' => $faker->randomFloat(2, 5, 100),
                   'is_physical' => $faker->boolean,
                   'sound_path' => $faker->url,
                   'file_path' => $faker->url,
                   'category_id' => $categories->random()->id, // Randomly assign a category
                   'language' => $faker->languageCode,
                   'rating' => $faker->randomFloat(1, 1, 5),
                   'raterscount' => $faker->numberBetween(1, 100),
                   'copies' => $faker->numberBetween(1, 20),
                   'publisher' => $faker->company,
               ]);
           }
       }
   }
