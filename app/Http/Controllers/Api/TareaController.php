<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoTarea;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTareaRequest;
use App\Http\Requests\UpdateTareaRequest;
use App\Http\Resources\TareaResource;
use App\Models\Tarea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TareaController extends Controller
{
    /**
     * GET /api/tareas?estado=pendiente&per_page=15
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::enum(EstadoTarea::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tareas = $request->user()
            ->tareas()
            ->estado($filtros['estado'] ?? null)
            ->orderBy('fecha_limite')
            ->orderBy('id')
            ->paginate($filtros['per_page'] ?? 15)
            ->withQueryString();

        return TareaResource::collection($tareas);
    }

    /**
     * POST /api/tareas
     */
    public function store(StoreTareaRequest $request): TareaResource
    {
        $tarea = $request->user()->tareas()->create($request->validated());

        return new TareaResource($tarea);
    }

    /**
     * GET /api/tareas/{tarea}
     */
    public function show(Tarea $tarea): TareaResource
    {
        Gate::authorize('view', $tarea);

        return new TareaResource($tarea);
    }

    /**
     * PUT|PATCH /api/tareas/{tarea}
     */
    public function update(UpdateTareaRequest $request, Tarea $tarea): TareaResource
    {
        Gate::authorize('update', $tarea);

        $tarea->update($request->validated());

        return new TareaResource($tarea);
    }

    /**
     * DELETE /api/tareas/{tarea}
     */
    public function destroy(Tarea $tarea): JsonResponse
    {
        Gate::authorize('delete', $tarea);

        $tarea->delete();

        return response()->json(['message' => 'Tarea eliminada correctamente.']);
    }
}
