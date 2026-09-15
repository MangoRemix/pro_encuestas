<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production') && ! env('SEED_DEMO_ACCOUNTS', false)) {
            $this->command?->warn(
                'PersonSeeder: omitido en producción (define SEED_DEMO_ACCOUNTS=true para forzarlo explícitamente).'
            );

            return;
        }

        $defaultPassword = env('DEFAULT_SEED_PASSWORD', 'CambiarEsta123!');

        $persons = [
            [
                'name' => 'Administrador',
                'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
                'sex_id' => 1,
                'age' => 18,
                'parish_id' => 1,
                'password' => env('ADMIN_PASSWORD', $defaultPassword),
                'rol_name' => 'ADMIN',
            ],
        ];

        for ($i = 1; $i <= 10; $i++) {
            $persons[] = [
                'name' => "Encuestador {$i}",
                'email' => "encuestador{$i}@email.com",
                'sex_id' => 1,
                'age' => 18,
                'parish_id' => 1,
                'password' => $defaultPassword,
                'rol_name' => 'POLLSTER',
            ];
        }

        foreach ($persons as $personData) {
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
