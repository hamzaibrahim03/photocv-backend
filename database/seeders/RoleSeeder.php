<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'club_admin',
            'member',
            'Chairperson',
            'Secretary',
            'Treasurer',
            'Programme Secretary',
            'Membership Secretary',
            'Internal Comp Secretary',
            'External Comp Secretary',
            'Webmaster',
            'IT Manager',
            'Publicity & Events Officer',
            'General Members',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
