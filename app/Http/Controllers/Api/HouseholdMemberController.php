<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Household;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class HouseholdMemberController extends Controller
{
    /**
     * Endpoint para consultar miembros del hogar
     */
    public function index(Household $household)
    {
        $this->authorizeAccess($household);

        $members = $household->users()->get();

        return response()->json(UserResource::collection($members));
    }

    /**
     * Endpoint para unirse a hogar por código de invitación
     */
    public function joinByCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),  [
                'invitation_code' => ['required', 'string']
            ]);
            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $household = Household::where('invitation_code', strtoupper($request->invitation_code))->first();

            if (!$household) {
                return response()->json(['message' => 'Código de invitación invalido'], 404);
            }

            if (!$household->allow_invitations) {
                return response()->json(['message' => 'Invitación al hogar inhabilitada'], 403);
            }

            if ($household->invitation_expires_at && now()->greaterThan($household->invitation_expires_at)) {
                return response()->json(['message' => 'La invitación ha expirado'], 422);
            }

            $alreadyMember = $household->users()
                ->where('user_id', auth()->id())
                ->exists();

            if ($alreadyMember) {
                return response()->json(['message' => 'Ya eres miembro'], 422);
            }

            $household->users()->attach(auth()->id(), ['role' => 'member']);

            return response()->json([
                'message' => 'Ahora formas parte de este hogar',
                'data' => [
                    'household' => new HouseholdResource($household)
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al vincularse al hogar'], 500);
        }
    }

    /**
     * Endpoint para re-generar código de invitación
     */
    public function regenerateCode(Household $household)
    {

        $this->authorizeOwner($household);

        $household->update([
            'invitation_code' => strtoupper('HOME-' . Str::random(6)),
            'invitation_expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'message' => 'Código de invitación regenerado',
            'data' => [
                'invitation_code' => $household->invitation_code,
            ]
        ]);
    }

    public function invitation(Household $household)
    {

        $this->authorizeManageMembers($household);

        return response()->json([
            'data' => [
                'invitation_code' => $household->invitation_code,
                'expires_at' => $household->invitation_expires_at,
                'qr_value' => json_encode([
                    'type' => 'household_invitation',
                    'code' => $household->invitation_code,
                ]),
            ]
        ]);
    }

    /**
     * Endpoint para actualizar miembro
     */
    public function update(Request $request, Household $household, User $user)
    {

        $this->authorizeOwner($household);

        try {
            $validator = Validator::make($request->all(), [
                'role' => ['required', Rule::in(['admin', 'member',]),]
            ]);
            if ($validator->fails())
                return response()->json([$validator->errors()->first()], 400);

            $memberExists = $household->users()
                ->where('user_id', $user->id)
                ->exists();

            if (!$memberExists) {
                return response()->json([
                    'message' => 'El usuario no es miembro del hogar'
                ], 404);
            }

            $household->users()->updateExistingPivot($user->id, ['role' => $request->role]);

            return response()->json([
                'message' => 'Miembro actualizado'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar miembro'], 500);
        }
    }

    /**
     * Endpoint para eliminar miembro
     */
    public function destroy(Household $household, User $user)
    {

        $this->authorizeOwner($household);

        $memberExists = $household->users()
            ->where('user_id', $user->id)
            ->exists();

        if (!$memberExists) {
            return response()->json(['message' => 'Miembro no localizado'], 404);
        }

        $isOwner = $household->users()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if ($isOwner) {
            return response()->json(['message' => 'No se puede eliminar al propietario del hogar'], 422);
        }

        $household->users()->detach($user->id);

        return response()->json(['message' => 'Miembro eliminado']);
    }

    /**
     * Endpoint para que un miembro pueda salir del hogar
     */
    public function leave(Household $household)
    {
        $member = $household->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No eres miembro al hogar seleccionado'], 404);
        }

        $role = $member->pivot->role;

        if ($role === 'owner') {
            return response()->json(['message' => 'El propietario no puede abandonar el hogar'], 422);
        }

        $household->users()->detach(auth()->id());

        return response()->json(['message' => 'Has abandonado el hogar']);
    }

    /**
     * Verificar permiso de acceso a miembros del hogar
     */
    private function authorizeAccess(Household $household): void
    {
        $exists = $household->users()
            ->where('user_id', auth()->id())
            ->exists();

        abort_unless($exists, 403, 'No autorizado');
    }

    /**
     * Verificar permisos miembros de gestión autorizados
     */
    private function authorizeManageMembers(Household $household): void
    {

        $allowed = $household->users()
            ->where('user_id', auth()->id())
            ->wherePivotIn('role', [
                'owner',
                'admin'
            ])
            ->exists();

        abort_unless($allowed, 403, 'Permisos insuficientes');
    }

    /**
     * Verificar permiso de propietario del hogar
     */
    private function authorizeOwner(Household $household): void
    {

        $isOwner = $household->users()
            ->where('user_id', auth()->id())
            ->wherePivot('role', 'owner')
            ->exists();

        abort_unless($isOwner, 403, 'Solo los propietarios pueden realizar esta acción');
    }
}
