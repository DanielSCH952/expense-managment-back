<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => [
                    'required',
                    'confirmed',
                    'min:8'
                ],
            ];
            $messages = [
                'name.required' => 'Nombre requerido.',
                'name.string' => 'Nombre debe ser una conjunto de caracteres.',
                'name.max' => 'Nombre debe tener un máximo de 255 caracteres.',
                'email.required' => 'Correo requerido.',
                'email.email' => 'Correo en formato invalido.',
                'email.unique' => 'Correo ya se encuentra registrado.',
                'password.required' => 'Contraseña requerida.',
                'password.confirmed' => 'Confirmación de contraseña no coincide.',
                'password.min' => 'Contraseña debe tener mínimo 8 caracteres',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);


            $user = User::create([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('mobile-token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                ]
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al registrar cuenta'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ];
            $messages = [
                'email.required' => 'Correo requerido.',
                'email.email' => 'Correo en formato invalido.',
                'password.required' => 'Contraseña requerida.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {

                return response()->json([
                    'message' => 'Credenciales invalidas.'
                ], 401);
            }

            $token = $user->createToken('mobile-token')->plainTextToken;

            return response()->json([
                'message' => 'Sesión iniciada.',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al iniciar sesión'], 500);
        }
    }

    public function me()
    {
        return response()->json([
            'data' => new UserResource(auth()->user())
        ]);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada.'
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')
                        ->ignore(auth()->id())
                ],
            ];
            $messages = [
                'name.required' => 'Nombre requerido.',
                'name.string' => 'Nombre debe ser una conjunto de caracteres.',
                'name.max' => 'Nombre debe tener un máximo de 255 caracteres.',
                'email.required' => 'Correo requerido.',
                'email.email' => 'Correo en formato invalido.',
                'email.unique' => 'Correo ya se encuentra registrado.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $user = auth()->user();

            $user->update($validator->validated());

            return response()->json([
                'message' => 'Perfil actualizado.',
                'data' => new UserResource($user)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar información del perfil.'], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $rules = [
                'current_password' => ['required'],
                'password' => [
                    'required',
                    'confirmed',
                    'min:8'
                ]
            ];
            $messages = [
                'password.required' => 'Contraseña requerida.',
                'password.confirmed' => 'Confirmación de contraseña no coincide.',
                'password.min' => 'Contraseña debe tener mínimo 8 caracteres',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {

                return response()->json([
                    'message' => 'Contraseña actual incorrecta.'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'Contraseña actualizada'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar contraseña'], 500);
        }
    }
}
