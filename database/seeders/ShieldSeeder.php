<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_contribution","view_any_contribution","create_contribution","update_contribution","restore_contribution","restore_any_contribution","replicate_contribution","reorder_contribution","delete_contribution","delete_any_contribution","force_delete_contribution","force_delete_any_contribution","view_house","view_any_house","create_house","update_house","restore_house","restore_any_house","replicate_house","reorder_house","delete_house","delete_any_house","force_delete_house","force_delete_any_house","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","widget_HouseOverview"]},{"name":"admin","guard_name":"web","permissions":["view_contribution","view_any_contribution","create_contribution","update_contribution","replicate_contribution","reorder_contribution","delete_contribution","delete_any_contribution","view_house","view_any_house","create_house","update_house","replicate_house","reorder_house","delete_house","delete_any_house","view_user","view_any_user","create_user","update_user","widget_HouseOverview"]},{"name":"karang_taruna","guard_name":"web","permissions":["view_contribution","view_any_contribution","update_contribution","view_house","view_any_house","widget_HouseOverview"]}]';
        $directPermissions = '{"18":{"name":"view_block","guard_name":"web"},"19":{"name":"view_any_block","guard_name":"web"},"20":{"name":"create_block","guard_name":"web"},"21":{"name":"update_block","guard_name":"web"},"22":{"name":"restore_block","guard_name":"web"},"23":{"name":"restore_any_block","guard_name":"web"},"24":{"name":"replicate_block","guard_name":"web"},"25":{"name":"reorder_block","guard_name":"web"},"26":{"name":"delete_block","guard_name":"web"},"27":{"name":"delete_any_block","guard_name":"web"},"28":{"name":"force_delete_block","guard_name":"web"},"29":{"name":"force_delete_any_block","guard_name":"web"}}';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
