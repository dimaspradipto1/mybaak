<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = \App\Models\Role::all()->mapWithKeys(function ($role) {
            return [strtoupper($role->nama_role) => $role->id];
        });

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@uis.ac.id',
                'password' => Hash::make('852456dimas'),
                'role_id' => $roles['SUPER ADMIN'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@uis.ac.id',
                'password' => Hash::make('mybaakuis'),
                'role_id' => $roles['ADMIN'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Leni Utami, S.Si.,M.KM',
                'email' => 'leni@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['APPROVAL'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Andi Hidayatul Fadlilah, SE,M. Si.AK',
                'email' => 'andihidayatul@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['APPROVAL'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Staff BAAK',
                'email' => 'baak@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['KEPALA. BAAK'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Mahasiswa',
                'email' => 'mahasiswa@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['MAHASISWA'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Mahasiswa 2',
                'email' => 'mahasiswa2@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['MAHASISWA'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Tata Usaha rektorat',
                'email' => 'tatausaha@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['TATA USAHA REKTORAT'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'M. Ansar',
                'email' => 'ansar@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['TATA USAHA FST'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'TU FEB',
                'email' => 'feb@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['TATA USAHA FEB'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'TU FIKES',
                'email' => 'fikes@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['TATA USAHA FIKES'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Army Trilidia Devega, S.Kom, M.Pd.T',
                'email' => 'army@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['APPROVAL'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Afrina, S.Kom, M.SI',
                'email' => 'afrina@uis.ac.id',
                'password' => Hash::make('password'),
                'role_id' => $roles['APPROVAL'] ?? null,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            if ($userData['role_id'] === null) {
                continue;
            }

            $userData['created_at'] = now();
            $userData['updated_at'] = now();

            $user = \App\Models\User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            \App\Models\Profile::firstOrCreate([
                'users_id' => $user->id
            ]);
        }
    }
}
