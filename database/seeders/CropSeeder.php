<?php

namespace Database\Seeders;

use App\Models\Crop;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    /**
     * Seed common Indonesian crops with HST data.
     */
    public function run(): void
    {
        $crops = [
            [
                'name' => 'Padi',
                'variety' => 'IR64',
                'default_hst' => 115,
                'description' => 'Padi varietas IR64, cocok untuk dataran rendah hingga menengah. Hasil panen rata-rata 5-7 ton/ha.',
                'estimated_yield_per_plant_kg' => 0.05,
                'icon' => '🌾',
            ],
            [
                'name' => 'Jagung',
                'variety' => 'Hibrida',
                'default_hst' => 90,
                'description' => 'Jagung hibrida dengan produktivitas tinggi. Cocok untuk lahan kering dan tadah hujan.',
                'estimated_yield_per_plant_kg' => 0.3,
                'icon' => '🌽',
            ],
            [
                'name' => 'Cabai Merah',
                'variety' => 'Keriting',
                'default_hst' => 120,
                'description' => 'Cabai merah keriting, favorit pasar tradisional. Panen bertahap selama 3-4 bulan.',
                'estimated_yield_per_plant_kg' => 0.8,
                'icon' => '🌶️',
            ],
            [
                'name' => 'Tomat',
                'variety' => 'Servo',
                'default_hst' => 75,
                'description' => 'Tomat varietas Servo, buah besar dan cocok untuk pasar segar.',
                'estimated_yield_per_plant_kg' => 2.5,
                'icon' => '🍅',
            ],
            [
                'name' => 'Bawang Merah',
                'variety' => 'Bima Brebes',
                'default_hst' => 65,
                'description' => 'Bawang merah varietas Bima Brebes, umbi sedang dengan rasa pedas khas.',
                'estimated_yield_per_plant_kg' => 0.1,
                'icon' => '🧅',
            ],
            [
                'name' => 'Kangkung',
                'variety' => 'Lokal',
                'default_hst' => 25,
                'description' => 'Kangkung darat, tanaman cepat panen dan mudah dibudidayakan.',
                'estimated_yield_per_plant_kg' => 0.2,
                'icon' => '🥬',
            ],
            [
                'name' => 'Bayam',
                'variety' => 'Hijau',
                'default_hst' => 21,
                'description' => 'Bayam hijau, sayuran bergizi tinggi dengan masa panen singkat.',
                'estimated_yield_per_plant_kg' => 0.2,
                'icon' => '🥬',
            ],
            [
                'name' => 'Kacang Panjang',
                'variety' => 'Lokal',
                'default_hst' => 60,
                'description' => 'Kacang panjang, panen bertahap. Cocok untuk tumpang sari.',
                'estimated_yield_per_plant_kg' => 1.0,
                'icon' => '🫘',
            ],
            [
                'name' => 'Mentimun',
                'variety' => 'Lokal',
                'default_hst' => 45,
                'description' => 'Mentimun lokal, tanaman merambat dengan panen cepat.',
                'estimated_yield_per_plant_kg' => 3.0,
                'icon' => '🥒',
            ],
            [
                'name' => 'Terong',
                'variety' => 'Ungu',
                'default_hst' => 70,
                'description' => 'Terong ungu, buah panjang dan cocok untuk berbagai masakan.',
                'estimated_yield_per_plant_kg' => 2.0,
                'icon' => '🍆',
            ],
        ];

        foreach ($crops as $crop) {
            Crop::updateOrCreate(['name' => $crop['name']], $crop);
        }
    }
}
