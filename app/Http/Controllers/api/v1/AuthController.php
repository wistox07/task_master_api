<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Termwind\Components\Raw;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => [
                "required",
                "string",
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => true,
                "message" => "Error en validación",
                "message_detail" => $validator->errors()->all()
            ], 400);
        }

        $email = $request->input("email");
        $password = $request->input("password");


        try {

            $user = User::where("email", $email)->first();
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "El usuario ingresado no se encuentra registrado"
                ], 400);
            }

            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    "error" => true,
                    "message" => "Credenciales incorrectas",
                    "message_detail" => "Las credenciales ingresadas son incorrectas"
                ], 400);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                "error" => false,
                "message" => "Inicio de sesión exitoso",
                "user" => [
                    "name" => $user->name,
                    "email" => $user->email,
                ],
                "token" => $token
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error en el proceso de logueo",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users",
            "name" => "required|string",
            "password" => [
                "required",
                "string",
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => true,
                "message" => "Error en validación",
                "message_detail" => $validator->errors()->all()
            ], 400);
        }

        $email = $request->input("email");
        $password = $request->input("password");
        $name = $request->input("name");

        try {
            $user = User::create([
                "name" => $name,
                "email" => $email,
                "password" => Hash::make($password)
            ]);

            return response()->json([
                "error" => false,
                "message" => "Registro de Usuario exitoso",
                "user" => [
                    "name" => $user->name,
                    "email" => $user->email,
                    "fecha_creacion" => $user->created_at->format("Y-m-d H:i:s")
                ]
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error en el proceso de registro",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }
}
