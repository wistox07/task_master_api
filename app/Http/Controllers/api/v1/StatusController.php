<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class StatusController extends Controller
{
    public function listStatuses(Request $request)
    {
        try {

            $statuses = Status::all();
            if (!$statuses) {
                return response()->json([
                    "error" => true,
                    "message" => "Lista de estados no encontrados",
                    "message_detail" => "No fue posible encontrar los estados  de tareas"
                ], 404);
            }

            return response()->json([
                "error" => false,
                "message" => "Lista de estados obtenidas correctamente",
                "statuses" => StatusResource::collection($statuses)
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error al listar estados",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }
}
