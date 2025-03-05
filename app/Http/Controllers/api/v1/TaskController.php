<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API de Tareas",
 *      description="Documentación de la API de tareas",
 *      @OA\Contact(
 *          email="soporte@tudominio.com"
 *      ),
 * )
 * 
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Servidor de desarrollo"
 * )
 *
 * @OA\PathItem(path="/api/tasks")
 */


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listTasks(Request $request)
    {
        /**
         * @OA\Get(
         *     path="/api/tasks",
         *     summary="Lista todas las tareas",
         *     tags={"Tasks"},
         *     @OA\Response(
         *         response=200,
         *         description="Lista de tareas"
         *     )
         * )
         */

        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");

            $user = User::find($userIdLogued);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "No fue posible encontrar al usuario logueado"
                ], 401);
            }

            $tasks = $user->tasks()->with('status')->get();
            return response()->json([
                "error" => false,
                "message" => "Lista de tareas obtenidas correctamente",
                "tasks" => TaskResource::collection($tasks)
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error al listar tareas",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string",
            "description" => "required|string",
            "expiration_date" => "required|date",
            "status_id" => "required|int"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => true,
                "message" => "Error en validación",
                "message_detail" => $validator->errors()->all()
            ], 400);
        }
        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");

            $user = User::find($userIdLogued);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "No fue posible encontrar al usuario logueado"
                ], 401);
            }


            $task = new Task();
            $task->title = $request->input("title");
            $task->description = $request->input("description");
            $task->expiration_date = $request->input("expiration_date");
            $task->status_id = $request->input("status_id");
            $task->user_id = $userIdLogued;
            $task->save();

            if (!$task) {
                return response()->json([
                    "error" => true,
                    "message" => "Tarea no creada",
                    "message_detail" => "No fue posible realizar la creación de la tarea"
                ], 500); // 500 Internal Server Error
            }

            return response()->json([
                "error" => false,
                "message" => "Tarea creada correctamente",
                "task" => new TaskResource($task)
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error al guardar tarea",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");

            $user = User::find($userIdLogued);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "No fue posible encontrar al usuario logueado"
                ], 401);
            }

            $task = $user->tasks()->with('status')->find($id);
            if (!$task) {
                return response()->json([
                    "error" => true,
                    "message" => "Tarea no encontrada",
                    "message_detail" => "No fue posible encontrar la tarea solicitada"
                ], 404);
            }


            return response()->json([
                "error" => false,
                "message" => "Tarea obtenida correctamente",
                "task" =>  new TaskResource($task)
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error al obtener la tarea",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");

            $user = User::find($userIdLogued);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "No fue posible encontrar al usuario logueado"
                ], 401);
            }

            $task = $user->tasks()->find($id);
            if (!$task) {
                return response()->json([
                    "error" => true,
                    "message" => "Tarea no encontrada",
                    "message_detail" => "No fue posible encontrar la tarea solicitada"
                ], 404);
            }

            $task->title = $request->input("title");
            $task->description = $request->input("description");
            $task->expiration_date = $request->input("expiration_date");
            $task->status_id = $request->input("status_id");
            $task->user_id = $userIdLogued;
            $task->save();


            return response()->json([
                "error" => false,
                "message" => "Tarea actualizada correctamente",
                "task" =>  new TaskResource($task)
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error al actualizar la tarea",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");

            $user = User::find($userIdLogued);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "No fue posible encontrar al usuario logueado"
                ], 401);
            }


            $task = $user->tasks()->find($id);
            if (!$task) {
                return response()->json([
                    "error" => true,
                    "message" => "Tarea no encontrada",
                    "message_detail" => "No fue posible encontrar la tarea solicitada"
                ], 404);
            }

            $task->delete();

            return response()->json([
                "error" => false,
                "message" => "Tarea eliminada correctamente"
            ]);
        } catch (Throwable $ex) {
            return response()->json([
                "error" => true,
                "message" => "Error al eliminar la tarea",
                "message_detail" => $ex->getMessage()
            ], 500);
        }
    }
}
