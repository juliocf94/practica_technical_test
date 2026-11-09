<?php

namespace Tests\Feature;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TareaApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    private function datosValidos(array $override = []): array
    {
        return array_merge([
            'titulo' => 'Preparar entrega',
            'descripcion' => 'Subir el proyecto a GitHub',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addWeek()->format('Y-m-d'),
        ], $override);
    }

    // ---------------------------------------------------------------- index

    public function test_lista_solo_las_tareas_del_usuario_autenticado(): void
    {
        Tarea::factory()->count(3)->for($this->user)->create();
        Tarea::factory()->count(2)->create(); // de otro usuario

        $this->getJson('/api/tareas')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'titulo', 'descripcion', 'estado', 'fecha_limite', 'created_at', 'updated_at']],
                'links',
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_filtra_por_estado(): void
    {
        Tarea::factory()->count(2)->pendiente()->for($this->user)->create();
        Tarea::factory()->count(3)->completada()->for($this->user)->create();

        $this->getJson('/api/tareas?estado=completada')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.estado', 'completada');
    }

    public function test_rechaza_un_filtro_de_estado_invalido(): void
    {
        $this->getJson('/api/tareas?estado=inventado')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    // ---------------------------------------------------------------- store

    public function test_crea_una_tarea(): void
    {
        $datos = $this->datosValidos();

        $this->postJson('/api/tareas', $datos)
            ->assertCreated()
            ->assertJsonPath('data.titulo', $datos['titulo'])
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.fecha_limite', $datos['fecha_limite']);

        $this->assertDatabaseHas('tareas', [
            'titulo' => $datos['titulo'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_la_descripcion_es_opcional(): void
    {
        $this->postJson('/api/tareas', $this->datosValidos(['descripcion' => null]))
            ->assertCreated()
            ->assertJsonPath('data.descripcion', null);
    }

    public static function datosInvalidos(): array
    {
        return [
            'sin título' => [['titulo' => ''], 'titulo'],
            'título demasiado largo' => [['titulo' => str_repeat('a', 256)], 'titulo'],
            'sin estado' => [['estado' => ''], 'estado'],
            'estado fuera del enum' => [['estado' => 'archivada'], 'estado'],
            'sin fecha límite' => [['fecha_limite' => ''], 'fecha_limite'],
            'fecha con formato incorrecto' => [['fecha_limite' => '31/12/2030'], 'fecha_limite'],
            'fecha en el pasado' => [['fecha_limite' => '2000-01-01'], 'fecha_limite'],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_valida_los_datos_al_crear(array $override, string $campo): void
    {
        $this->postJson('/api/tareas', $this->datosValidos($override))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($campo);

        $this->assertDatabaseCount('tareas', 0);
    }

    // ---------------------------------------------------------------- show

    public function test_muestra_el_detalle_de_una_tarea(): void
    {
        $tarea = Tarea::factory()->for($this->user)->create();

        $this->getJson("/api/tareas/{$tarea->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $tarea->id)
            ->assertJsonPath('data.titulo', $tarea->titulo);
    }

    public function test_devuelve_404_si_la_tarea_no_existe(): void
    {
        $this->getJson('/api/tareas/9999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Recurso no encontrado.');
    }

    // ---------------------------------------------------------------- update

    public function test_actualiza_una_tarea(): void
    {
        $tarea = Tarea::factory()->pendiente()->for($this->user)->create();

        $this->putJson("/api/tareas/{$tarea->id}", $this->datosValidos([
            'titulo' => 'Título actualizado',
            'estado' => 'en_progreso',
        ]))
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Título actualizado')
            ->assertJsonPath('data.estado', 'en_progreso');

        $this->assertDatabaseHas('tareas', ['id' => $tarea->id, 'estado' => 'en_progreso']);
    }

    public function test_permite_actualizar_parcialmente(): void
    {
        $tarea = Tarea::factory()->pendiente()->for($this->user)->create();

        $this->patchJson("/api/tareas/{$tarea->id}", ['estado' => 'completada'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'completada')
            ->assertJsonPath('data.titulo', $tarea->titulo);
    }

    public function test_valida_los_datos_al_actualizar(): void
    {
        $tarea = Tarea::factory()->for($this->user)->create();

        $this->putJson("/api/tareas/{$tarea->id}", ['titulo' => '', 'estado' => 'archivada'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titulo', 'estado']);
    }

    // ---------------------------------------------------------------- destroy

    public function test_elimina_una_tarea(): void
    {
        $tarea = Tarea::factory()->for($this->user)->create();

        $this->deleteJson("/api/tareas/{$tarea->id}")
            ->assertOk()
            ->assertExactJson(['message' => 'Tarea eliminada correctamente.']);

        $this->assertModelMissing($tarea);
    }

    // ---------------------------------------------------------------- autorización

    public function test_no_puede_acceder_a_tareas_de_otro_usuario(): void
    {
        $ajena = Tarea::factory()->create();

        $this->getJson("/api/tareas/{$ajena->id}")->assertForbidden();
        $this->putJson("/api/tareas/{$ajena->id}", ['titulo' => 'Hackeada'])->assertForbidden();
        $this->deleteJson("/api/tareas/{$ajena->id}")->assertForbidden();

        $this->assertModelExists($ajena);
    }
}
