<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_rutas_de_tareas_requieren_autenticacion(): void
    {
        $this->getJson('/api/tareas')->assertUnauthorized();
        $this->postJson('/api/tareas', [])->assertUnauthorized();
    }

    public function test_responde_401_en_json_aunque_el_cliente_no_envie_accept_json(): void
    {
        $this->get('/api/tareas')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_login_devuelve_un_token_valido(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com']);

        $token = $this->postJson('/api/login', [
            'email' => 'ana@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email']])
            ->json('token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_login_rechaza_credenciales_incorrectas(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/login', [
            'email' => 'ana@example.com',
            'password' => 'incorrecta',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_registro_crea_usuario_y_devuelve_token(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ])
            ->assertCreated()
            ->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
    }

    public function test_logout_revoca_el_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
