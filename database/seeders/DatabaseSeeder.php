<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        $defaultCategories = [
            ['name' => 'Elektronik', 'icon' => 'device-phone-mobile'],
            ['name' => 'Pakaian & Mode', 'icon' => 'tag'],
            ['name' => 'Makanan & Minuman', 'icon' => 'cake'],
            ['name' => 'Rumah Tangga', 'icon' => 'home'],
            ['name' => 'Hobi & Hiburan', 'icon' => 'sparkles'],
            ['name' => 'Kendaraan & Otomotif', 'icon' => 'truck'],
            ['name' => 'Jasa & Lainnya', 'icon' => 'briefcase'],
        ];

        foreach ($defaultCategories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
