<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use \App\Models\Person;
use App\Models\Product;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Product::factory(10)->create();
    }
}
