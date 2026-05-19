<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    private $persons = [
        [
            'name'=> 'Encuestador 1','email'=>'encuestador1@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 2','email'=>'encuestador2@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 3','email'=>'encuestador3@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 4','email'=>'encuestador4@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 5','email'=>'encuestador5@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 6','email'=>'encuestador6@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 7','email'=>'encuestador7@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 8','email'=>'encuestador8@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 9','email'=>'encuestador9@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],
        [
            'name'=> 'Encuestador 10','email'=>'encuestador10@email.com','sex_id' => 1, 'age_range_id' => 1, 'parish_id' => 1,
            'password' => '', 'rol_id' => 1
        ],

    ];
    
    public function run(): void
    {
        //
        foreach ($this->persons as $key => $person) {
            # code...
            
            $person['password'] = Hash::make('encuestador'.$key,['rounds'=>10]);

            Person::create($person);
        }
    }
}
