<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgeRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    private $values = [
        [
            'range' => '18 - 30'
        ],
        [
            'range' => '31 - 45'
        ],
        [
            'range' => '46 - 60'
        ],
        [
            'range' => '61 - *'
        ],
    ];

    public function run(): void
    {
        //
        foreach ($this->values as $AgeRange) {
            # code...
            DB::table('age_ranges')->insert($AgeRange);
        }
    }
}
