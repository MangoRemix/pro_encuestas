<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private $values = [
        [
            'abbreviation' => 'M',
            'description' => 'MALE',
        ],
        [
            'abbreviation' => 'F',
            'description' => 'FEMALE',
        ],
    ];
    public function run(): void
    {
        //
        foreach ($this->values as $sex) {
            DB::table('sexes')->insert($sex);
        }
    }
}
