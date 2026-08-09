<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Let;

class LetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Let::create([
            'broj_leta' => 'BEG123',
            'polaziste' => 'Beograd',
            'odrediste' => 'Pariz',
            'vreme_poletanja' => now()->addDays(2),
            'vreme_sletanja' => now()->addDays(2)->addHours(2),
            'broj_mesta' => 150,
            'cena' => 120.50,
        ]);

         Let::create([
            'broj_leta' => 'NYC456',
            'polaziste' => 'New York',
            'odrediste' => 'London',
            'vreme_poletanja' => now()->addDays(5),
            'vreme_sletanja' => now()->addDays(5)->addHours(7),
            'broj_mesta' => 300,
            'cena' => 450.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG201',
            'polaziste' => 'Beograd',
            'odrediste' => 'London',
            'vreme_poletanja' => now()->addDays(3),
            'vreme_sletanja' => now()->addDays(3)->addHours(2)->addMinutes(15),
            'broj_mesta' => 180,
            'cena' => 145.00,
        ]);

        Let::create([
            'broj_leta' => 'LON202',
            'polaziste' => 'London',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(7),
            'vreme_sletanja' => now()->addDays(7)->addHours(3)->addMinutes(15),
            'broj_mesta' => 180,
            'cena' => 152.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG305',
            'polaziste' => 'Beograd',
            'odrediste' => 'Rim',
            'vreme_poletanja' => now()->addDays(4),
            'vreme_sletanja' => now()->addDays(4)->addHours(1)->addMinutes(25),
            'broj_mesta' => 150,
            'cena' => 98.50,
        ]);

        Let::create([
            'broj_leta' => 'ROM306',
            'polaziste' => 'Rim',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(9),
            'vreme_sletanja' => now()->addDays(9)->addHours(1)->addMinutes(25),
            'broj_mesta' => 150,
            'cena' => 101.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG410',
            'polaziste' => 'Beograd',
            'odrediste' => 'Berlin',
            'vreme_poletanja' => now()->addDays(5),
            'vreme_sletanja' => now()->addDays(5)->addHours(1)->addMinutes(40),
            'broj_mesta' => 160,
            'cena' => 110.00,
        ]);

        Let::create([
            'broj_leta' => 'BER411',
            'polaziste' => 'Berlin',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(11),
            'vreme_sletanja' => now()->addDays(11)->addHours(1)->addMinutes(40),
            'broj_mesta' => 160,
            'cena' => 115.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG512',
            'polaziste' => 'Beograd',
            'odrediste' => 'Madrid',
            'vreme_poletanja' => now()->addDays(6),
            'vreme_sletanja' => now()->addDays(6)->addHours(3)->addMinutes(10),
            'broj_mesta' => 174,
            'cena' => 165.00,
        ]);

        Let::create([
            'broj_leta' => 'MAD513',
            'polaziste' => 'Madrid',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(13),
            'vreme_sletanja' => now()->addDays(13)->addHours(3)->addMinutes(15),
            'broj_mesta' => 174,
            'cena' => 172.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG614',
            'polaziste' => 'Beograd',
            'odrediste' => 'Amsterdam',
            'vreme_poletanja' => now()->addDays(8),
            'vreme_sletanja' => now()->addDays(8)->addHours(1)->addMinutes(55),
            'broj_mesta' => 168,
            'cena' => 128.00,
        ]);

        Let::create([
            'broj_leta' => 'AMS615',
            'polaziste' => 'Amsterdam',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(15),
            'vreme_sletanja' => now()->addDays(15)->addHours(2),
            'broj_mesta' => 168,
            'cena' => 133.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG716',
            'polaziste' => 'Beograd',
            'odrediste' => 'Istanbul',
            'vreme_poletanja' => now()->addDays(2)->addHours(6),
            'vreme_sletanja' => now()->addDays(2)->addHours(8)->addMinutes(15),
            'broj_mesta' => 189,
            'cena' => 89.00,
        ]);

        Let::create([
            'broj_leta' => 'IST717',
            'polaziste' => 'Istanbul',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(18),
            'vreme_sletanja' => now()->addDays(18)->addHours(1)->addMinutes(15),
            'broj_mesta' => 189,
            'cena' => 92.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG818',
            'polaziste' => 'Beograd',
            'odrediste' => 'Beč',
            'vreme_poletanja' => now()->addDays(3)->addHours(4),
            'vreme_sletanja' => now()->addDays(3)->addHours(4)->addMinutes(50),
            'broj_mesta' => 140,
            'cena' => 75.00,
        ]);

        Let::create([
            'broj_leta' => 'VIE819',
            'polaziste' => 'Beč',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(10),
            'vreme_sletanja' => now()->addDays(10)->addMinutes(55),
            'broj_mesta' => 140,
            'cena' => 79.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG920',
            'polaziste' => 'Beograd',
            'odrediste' => 'Atina',
            'vreme_poletanja' => now()->addDays(12),
            'vreme_sletanja' => now()->addDays(12)->addHours(1)->addMinutes(50),
            'broj_mesta' => 155,
            'cena' => 105.00,
        ]);

        Let::create([
            'broj_leta' => 'ATH921',
            'polaziste' => 'Atina',
            'odrediste' => 'Beograd',
            'vreme_poletanja' => now()->addDays(20),
            'vreme_sletanja' => now()->addDays(20)->addHours(1)->addMinutes(45),
            'broj_mesta' => 155,
            'cena' => 109.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG930',
            'polaziste' => 'Beograd',
            'odrediste' => 'Pariz',
            'vreme_poletanja' => now()->addDays(25),
            'vreme_sletanja' => now()->addDays(25)->addHours(2)->addMinutes(5),
            'broj_mesta' => 150,
            'cena' => 132.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG932',
            'polaziste' => 'Beograd',
            'odrediste' => 'London',
            'vreme_poletanja' => now()->addDays(16),
            'vreme_sletanja' => now()->addDays(16)->addHours(2)->addMinutes(15),
            'broj_mesta' => 180,
            'cena' => 139.00,
        ]);

        Let::create([
            'broj_leta' => 'BEG933',
            'polaziste' => 'Beograd',
            'odrediste' => 'Berlin',
            'vreme_poletanja' => now()->addDays(19),
            'vreme_sletanja' => now()->addDays(19)->addHours(1)->addMinutes(40),
            'broj_mesta' => 160,
            'cena' => 102.00,
        ]);


    }
}
