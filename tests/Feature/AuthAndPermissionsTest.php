<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_succeeds_with_valid_credentials_and_merges_direct_and_role_permissions(): void
    {
        $user = User::factory()->create([
            'username' => 'jdoe',
            'password' => 'secret123',
        ]);

        $directPerm = Permission::create(['label' => 'user-list', 'description' => 'List users']);
        $rolePerm = Permission::create(['label' => 'role-list', 'description' => 'List roles']);
        $role = Role::create(['label' => 'manager', 'display_label' => 'Manager']);

        $role->permissions()->attach($rolePerm->id);
        $user->roles()->attach($role->id);
        $user->permissions()->attach($directPerm->id);

        $response = $this->postJson('/api/login', [
            'username' => 'jdoe',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 200)
            ->assertJsonPath('body.response_data.user.username', 'jdoe')
            ->assertJsonPath('body.response_data.user.roles', ['manager'])
            ->assertJsonPath('body.response_data.user.permissions', ['user-list', 'role-list']);

        $this->assertNotEmpty($response->json('body.response_data.token'));
        $this->assertNotEmpty($response->json('body.response_data.expires_at'));
    }

    public function test_login_handles_null_sanctum_expiration_without_crashing(): void
    {
        config(['sanctum.expiration' => null]);

        User::factory()->create([
            'username' => 'noexpire',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'noexpire',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('body.response_data.expires_at', null);
    }

    public function test_login_fails_with_401_on_invalid_credentials(): void
    {
        User::factory()->create([
            'username' => 'jdoe',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'jdoe',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 401);
    }

    public function test_login_rate_limiter_blocks_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'username' => 'bruteforce_user',
                'password' => 'wrong',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'username' => 'bruteforce_user',
            'password' => 'wrong',
        ])->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 429);
    }

    public function test_validate_token_and_logout_flow(): void
    {
        $user = User::factory()->create([
            'username' => 'alice',
            'password' => 'password123',
        ]);
        $perm = Permission::create(['label' => 'permission-list']);
        $user->permissions()->attach($perm->id);

        $token = $user->createToken('alice', ['permission-list'])->plainTextToken;

        $validateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/validate-token');

        $validateResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('body.response_data.username', 'alice')
            ->assertJsonPath('body.response_data.permissions', ['permission-list']);

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_gate_authorization_and_immediate_permission_revocation(): void
    {
        $user = User::factory()->create();
        $listPerm = Permission::create(['label' => 'permission-list']);
        $user->permissions()->attach($listPerm->id);

        $token = $user->createToken('test-token', ['permission-list'])->plainTextToken;

        // 1. L'utilisateur a la permission 'permission-list' -> 200 OK
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/permission')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2. L'utilisateur n'a pas 'permission-create' -> 403 Forbidden
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/permission', ['label' => 'new-perm'])
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        // 3. Révocation de la permission en base de données -> effet immédiat (403 même avec l'ancien token)
        $user->permissions()->detach($listPerm->id);
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/permission')
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_permission_and_role_eloquent_relations_and_permission_role_unique_validation(): void
    {
        $admin = User::factory()->create();
        $permList = Permission::create(['label' => 'permission-list']);
        $permRoleCreate = Permission::create(['label' => 'permission_role-create']);
        $role = Role::create(['label' => 'editor', 'display_label' => 'Editor']);

        $admin->permissions()->attach([$permList->id, $permRoleCreate->id]);
        $admin->roles()->attach($role->id);
        $role->permissions()->attach($permList->id);

        // Vérifie que les relations Eloquent belongsToMany fonctionnent dans les deux sens
        $this->assertTrue($permList->roles->contains('id', $role->id));
        $this->assertTrue($permList->users->contains('id', $admin->id));
        $this->assertTrue($role->users->contains('id', $admin->id));
        $this->assertTrue($role->permissions->contains('id', $permList->id));

        $token = $admin->createToken('admin')->plainTextToken;

        // Création d'une nouvelle association permission_role valide -> 200 OK
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/permission_role', [
                'role_id' => $role->id,
                'permission_id' => $permRoleCreate->id,
            ]);

        $createResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Tentative de création d'un doublon (même role_id + permission_id) -> 422 Validation Error
        $duplicateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/permission_role', [
                'role_id' => $role->id,
                'permission_id' => $permRoleCreate->id,
            ]);

        $duplicateResponse->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422);
    }

    public function test_show_returns_404_when_resource_not_found_and_cascade_delete_works(): void
    {
        $admin = User::factory()->create();
        $permShow = Permission::create(['label' => 'user-show']);
        $permDelete = Permission::create(['label' => 'role-delete']);
        $role = Role::create(['label' => 'temp-role']);

        $admin->permissions()->attach([$permShow->id, $permDelete->id]);
        $admin->roles()->attach($role->id);
        $role->permissions()->attach($permShow->id);

        $token = $admin->createToken('admin')->plainTextToken;

        // 1. GET /api/user/99999 -> 404 Not Found
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user/99999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 404);

        // 2. DELETE /api/role/{id} supprime le rôle et cascade sur role_users / permission_roles sans erreur SQL
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/role/{$role->id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseMissing('role_users', ['role_id' => $role->id]);
        $this->assertDatabaseMissing('permission_roles', ['role_id' => $role->id]);
    }
}

