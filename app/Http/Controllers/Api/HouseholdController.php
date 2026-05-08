<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\Request;
use App\Http\Resources\HouseholdResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HouseholdController extends Controller
{
    /**
     * Endpoint para consultar el listado de hogares de un usuario
     */
    public function index(Request $request)
    {
        $currentPage = $request->page ?? 1;
        $households = $request->user()
            ->households()
            ->withCount('users')
            ->latest()
            ->paginate(10, ['*'], 'page', $currentPage);

        return response()->json([
            'data' => HouseholdResource::collection($households),
            'meta' => [
                'current_page' => $households->currentPage(),
                'last_page' => $households->lastPage(),
                'per_page' => $households->perPage(),
                'total' => $households->total(),
            ]
        ]);
    }

    /**
     * Endpoint para registrar un hogar
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json($validator->errors()->first(), 400);

        DB::beginTransaction();

        try {

            $household = Household::create([
                'name' => $request->name,
                'description' => $request->description,
                'invitation_code' => strtoupper('HOME-' . Str::random(6)),
                'invitation_expires_at' => now()->addDays(30),
                'created_by' => auth()->id(),
            ]);

            $household->users()->attach(
                auth()->id(),
                ['role' => 'owner']
            );

            DB::commit();

            return response()->json(['message' => 'Grupo creado', 'data' => new HouseholdResource($household->load('users'))], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el grupo', 'error' => $e->getMessage(),], 500);
        }
    }

    /**
     * Endpoint para consultar información de un hogar
     */
    public function show(Household $household)
    {
        $this->authorizeAccess($household);

        return response()->json([
            'data' => new HouseholdResource(
                $household->load([
                    'users',
                ])
            )
        ]);
    }

    /**
     * Endpoint para actualizar la información de un hogar
     */
    public function update(Request $request, Household $household)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json($validator->errors()->first(), 400);

        $this->authorizeOwner($household);

        $household->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Grupo actualizado',
            'data' => new HouseholdResource($household)
        ]);
    }

    /**
     * Endpoint para eliminar el registro de un hogar
     */
    public function destroy(Household $household)
    {
        $this->authorizeOwner($household);

        $household->delete();

        return response()->json([
            'message' => 'Grupo eliminado'
        ]);
    }

    private function authorizeAccess(Household $household): void
    {
        $exists = $household->users()
            ->where('user_id', auth()->id())
            ->exists();

        abort_unless($exists, 403, 'No autorizado');
    }

    private function authorizeOwner(Household $household): void
    {
        $isOwner = $household->users()
            ->where('user_id', auth()->id())
            ->wherePivot('role', 'owner')
            ->exists();

        abort_unless($isOwner, 403, 'Solo los dueños pueden acceder a esta acción');
    }
}
