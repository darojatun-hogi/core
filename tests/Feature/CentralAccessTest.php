<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AppAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Pengaturan user/role/permission terpusat di hub; "<app>.admin" hanya mengelola app miliknya
class CentralAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $_ENV['SSO_HUB'] = $_SERVER['SSO_HUB'] = 'true';

        parent::setUp();

        $this->seed(AppAccessSeeder::class);

        // Role & permission milik app client, seperti hasil AppAccessSeeder di masing-masing app
        foreach (['money', 'clip'] as $app) {
            Permission::firstOrCreate(['name' => "$app.access", 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => "$app.admin", 'guard_name' => 'web'])
                ->syncPermissions(["$app.access", ...config('access.shared_permissions')]);
            Role::firstOrCreate(['name' => "$app.user", 'guard_name' => 'web'])->syncPermissions(["$app.access"]);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SSO_HUB'], $_SERVER['SSO_HUB']);
    }

    private function makeUser(string $username, array $roles): User
    {
        $user = User::create([
            'name' => ucfirst($username),
            'username' => $username,
            'email' => "$username@example.com",
            'password' => 'secret-password',
        ]);
        $user->syncRoles($roles);
        $user->markEmailAsVerified();

        return $user;
    }

    public function test_app_admin_only_sees_users_of_their_app(): void
    {
        $admin = $this->makeUser('moneyadmin', ['core.user', 'money.admin']);
        $this->makeUser('budi', ['money.user']);
        $this->makeUser('sari', ['clip.user']);

        $this->actingAs($admin)->get('/users?app=money')
            ->assertOk()
            ->assertSee('budi@example.com')
            ->assertDontSee('sari@example.com');
    }

    public function test_app_admin_cannot_switch_to_another_app(): void
    {
        $admin = $this->makeUser('moneyadmin', ['core.user', 'money.admin']);

        $this->actingAs($admin)->get('/users?app=clip')->assertForbidden();
        $this->actingAs($admin)->get('/roles?app=core')->assertForbidden();
    }

    public function test_app_admin_cannot_edit_user_from_another_app(): void
    {
        $admin = $this->makeUser('moneyadmin', ['core.user', 'money.admin']);
        $clipOnly = $this->makeUser('sari', ['clip.user']);

        $this->actingAs($admin)->get('/users?app=money');
        $this->get("/users/{$clipOnly->id}/edit")->assertForbidden();
        $this->put("/users/{$clipOnly->id}", [
            'name' => 'Hacked', 'username' => 'sari', 'email' => 'sari@example.com', 'roles' => ['money.user'],
        ])->assertForbidden();

        $this->assertSame('Sari', $clipOnly->fresh()->name);
    }

    public function test_app_admin_assigns_roles_of_their_app_without_touching_other_apps(): void
    {
        $admin = $this->makeUser('moneyadmin', ['core.user', 'money.admin']);
        $user = $this->makeUser('budi', ['money.user', 'clip.user']);

        $this->actingAs($admin)->get('/users?app=money');
        $this->put("/users/{$user->id}", [
            'name' => 'Budi', 'username' => 'budi', 'email' => 'budi@example.com', 'roles' => ['money.admin'],
        ])->assertRedirect(route('users.index'));

        $this->assertEqualsCanonicalizing(['money.admin', 'clip.user'], $user->fresh()->getRoleNames()->all());

        // Role app lain tidak bisa diberikan dari konteks money
        $this->put("/users/{$user->id}", [
            'name' => 'Budi', 'username' => 'budi', 'email' => 'budi@example.com', 'roles' => ['clip.admin'],
        ])->assertSessionHasErrors('roles.0');
    }

    public function test_app_admin_only_edits_roles_of_their_app(): void
    {
        $admin = $this->makeUser('moneyadmin', ['core.user', 'money.admin']);

        $this->actingAs($admin)->get('/roles?app=money')->assertOk()->assertSee('money.user')->assertDontSee('clip.user');

        $clipRole = Role::findByName('clip.user');
        $this->get("/roles/{$clipRole->id}/edit")->assertNotFound();
    }

    public function test_super_admin_manages_every_app(): void
    {
        $super = $this->makeUser('owner', ['super-admin']);
        $this->makeUser('sari', ['clip.user']);

        $this->actingAs($super)->get('/users?app=clip')->assertOk()->assertSee('sari@example.com')->assertSee('Money');
    }

    public function test_plain_user_cannot_open_user_management(): void
    {
        $user = $this->makeUser('budi', ['core.user', 'money.user']);

        $this->actingAs($user)->get('/users')->assertForbidden();
    }
}
