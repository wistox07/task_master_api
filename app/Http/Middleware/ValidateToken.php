<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->header("token");
            if(!$token){
                return response()->json([
                    "error" => true,
                    "message" => "Token no enviado",
                    "message_detail" => "Es necesario enviar el token para continuar con el proceso"
                ],400);
            }

            $payload = JWTAuth::setToken($token)->getPayload();
            return $next($request);

        }catch (TokenExpiredException $e) {
            return response()->json([
                "error" => true,
                "message" => "Token expirado",
                "message_detail" => "El Token enviado ya ha expirado , por favor intente loguearse nuevamente"
            ],401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                "error" => true,
                "message" => "Token invalido",
                "message_detail" => "El Token enviado ya es invalido , por favor envie un token correcto"
            ],401);
        } catch (Throwable $e) {
            return response()->json([
                "error" => true,
                "message" => "Problema en validación de token",
                "message_detail" => $e->getMessage()
            ],500);
        }
    }
}
