<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private $values = [
        [
            'name' => 'ADMIN'
        ],
        [
            'name' => 'POLLSTER'
        ],
        [
            'name' => 'RESPONDENT'
        ]
    ];

    public function run(): void
    {
        //
        foreach ($this->values as $rol) {
            # code...
            DB::table('roles')->insert($rol);
        }
    }
}
