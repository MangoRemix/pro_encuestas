<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    private array $persons = [
        [
            'name'=> 'Administrador',
            'email'=>'admin@admin.com',
            'sex_id' => 1,
            'age_range_id' => 1,
            'parish_id' => 1,
            'password' => 'admin12345678',
            'rol_name' => 'ADMIN'
        ],
        [
            'name'=> 'Encuestador 1','email'=>'encuestador1@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador1', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 2','email'=>'encuestador2@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador2', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 3','email'=>'encuestador3@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador3', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 4','email'=>'encuestador4@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador4', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 5','email'=>'encuestador5@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador5', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 6','email'=>'encuestador6@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador6', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 7','email'=>'encuestador7@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador7', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 8','email'=>'encuestador8@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador8', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 9','email'=>'encuestador9@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador9', 'rol_name' => 'POLLSTER'
        ],
        [
            'name'=> 'Encuestador 10','email'=>'encuestador10@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => 'encuestador10', 'rol_name' => 'POLLSTER'
        ],

    ];
    
    public function run(): void
    {
        foreach ($this->persons as $personData) {
            $rolName = $personData['rol_name'];
            unset($personData['rol_name']);

            $rol = Rol::firstOrCreate(['name' => $rolName]);

            $personData['rol_id'] = $rol->id;
            $personData['password'] = Hash::make($personData['password']);

            Person::updateOrCreate(
                ['email' => $personData['email']],
                $personData
            );
        }
    }
}

