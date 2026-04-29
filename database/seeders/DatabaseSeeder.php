<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Seed crops first
        $this->call(CropSeeder::class);

        // Create demo farmer
        User::factory()->create([
            'name' => 'Pak Tani Demo',
            'email' => 'farmer@kebunku.test',
            'role' => 'farmer',
            'phone' => '081234567890',
            'address' => 'Desa Sukamaju, Kec. Ciamis, Jawa Barat',
            'latitude' => -7.3274,
            'longitude' => 108.3535,
        ]);

        // Create demo buyer
        User::factory()->create([
            'name' => 'Bu Pembeli Demo',
            'email' => 'buyer@kebunku.test',
            'role' => 'buyer',
            'phone' => '082345678901',
            'address' => 'Jl. Pasar Baru No. 10, Tasikmalaya',
        ]);
    }
}
