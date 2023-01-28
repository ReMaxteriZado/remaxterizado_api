<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ["name" => "categories", "slug" => "Categorías"],
            ["name" => "links", "slug" => "Enlaces"],
            ["name" => "codes", "slug" => "Códigos"],
            ["name" => "roles", "slug" => "Roles"],
            ["name" => "users", "slug" => "Usuarios"],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
            ]);
        }
    }
}
