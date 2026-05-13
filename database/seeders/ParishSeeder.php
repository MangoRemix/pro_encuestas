<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private $values = [
        [
            'name' => 'ALTAGRACIA',
        ],
        [
            'name' => 'VALENTIN VALIENTE',
        ],
        [
            'name' => 'AYACUCHO',
        ],
        [
            'name' => 'SANTA INES',
        ]
    ];

    public function run(): void
    {
        //
        foreach ($this->values as $parish) {
            # code...
            DB::table('parishes')->insert($parish);
        }
        
    }
}
