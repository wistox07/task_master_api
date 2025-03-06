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
use OpenApi\Annotations as OA;

/**
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor API principal"
 * )
 */

class AuthController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/v1/auth/login",
 *     summary="Iniciar sesión",
 *     description="Autentica a un usuario con correo y contraseña, devolviendo un token JWT.",
 *     operationId="login",
 *     tags={"Autenticación"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Inicio de sesión exitoso",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Inicio de sesión exitoso"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="name", type="string", example="Juan Pérez"),
 *                 @OA\Property(property="email", type="string", example="usuario@example.com")
 *             ),
 *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error en validación",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Error en validación"),
 *             @OA\Property(
 *                 property="message_detail",
 *                 type="array",
 *                 @OA\Items(type="string"),
 *                 example={
 *                     "El campo email es obligatorio.",
 *                     "El campo password es obligatorio.",
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuario no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Usuario no encontrado"),
 *             @OA\Property(property="message_detail", type="string", example="El usuario ingresado no se encuentra registrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Credenciales incorrectas",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Credenciales incorrectas"),
 *             @OA\Property(property="message_detail", type="string", example="Las credenciales ingresadas son incorrectas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error en el proceso de logueo",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Error en el proceso de logueo"),
 *             @OA\Property(property="message_detail", type="string", example="Mensaje de error interno")
 *         )
 *     )
 * )
 */

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
                ], 404);
            }

            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    "error" => true,
                    "message" => "Credenciales incorrectas",
                    "message_detail" => "Las credenciales ingresadas son incorrectas"
                ], 401);
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

    /**
 * @OA\Post(
 *     path="/api/v1/auth/register",
 *     summary="Registrar un nuevo usuario",
 *     description="Crea una nueva cuenta de usuario con nombre, email y contraseña.",
 *     operationId="register",
 *     tags={"Autenticación"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "name", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
 *             @OA\Property(property="name", type="string", example="Juan Pérez"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Registro exitoso",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Registro de Usuario exitoso"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="name", type="string", example="Juan Pérez"),
 *                 @OA\Property(property="email", type="string", example="usuario@example.com"),
 *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2024-03-05 14:30:00")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error en validación",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Error en validación"),
 *             @OA\Property(
 *                 property="message_detail",
 *                 type="array",
 *                 @OA\Items(type="string"),
 *                 example={
 *                     "El campo email es obligatorio.",
 *                     "El campo name es obligatorio",
 *                     "El campo password es obligatorio.",
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error en el proceso de registro",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Error en el proceso de registro"),
 *             @OA\Property(property="message_detail", type="string", example="Mensaje de error interno")
 *         )
 *     )
 * )
 */

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
