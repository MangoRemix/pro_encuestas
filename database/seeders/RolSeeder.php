<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    private $values = [
        ['name' => 'ADMIN'],
        ['name' => 'POLLSTER'],
        ['name' => 'RESPONDENT']
    ];

    public function run(): void
    {
        foreach ($this->values as $rol) {
            DB::table('roles')->updateOrInsert(
                ['name' => $rol['name']],
                $rol
            );
        }
    }
}
