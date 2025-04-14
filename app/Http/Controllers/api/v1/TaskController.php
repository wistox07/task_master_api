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
use OpenApi\Annotations as OA;



/**
 * @OA\Info(
 *     title="API task_master_api",
 *     version="1.0",
 *     description="Documentación de la API para la gestión de tareas."
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor API principal"
 * )
 */


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\Get(
     *     path="/api/v1/tasks/me",
     *     summary="Obtener lista de tareas del usuario autenticado",
     *     description="Retorna una lista de tareas asociadas al usuario autenticado, requiere un token JWT en los headers.",
     *     tags={"Tareas"},
     *      @OA\Parameter(
     *         name="token",
     *         in="header",
     *         required=true,
     *         description="Token JWT del usuario autenticado",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tareas obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lista de tareas obtenidas correctamente"),
     *             @OA\Property(
     *                 property="tasks",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Task")

 *             )
 *         )
 *     ),
 *  *     @OA\Response(
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
 *                     "El campo per_page es obligatorio.",
 *                     "El campo page es obligatorio."
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuario no autorizado o no encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Usuario no encontrado"),
 *             @OA\Property(property="message_detail", type="string", example="No fue posible encontrar al usuario logueado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Error al listar tareas"),
 *             @OA\Property(property="message_detail", type="string", example="Detalles del error interno")
 *         )
 *     )
 * 
 * )
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Tarea",
 *     description="Estructura de una tarea",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Deserunt ut."),
 *     @OA\Property(property="description", type="string", example="Descripción de la tarea."),
 *     @OA\Property(property="created_date", type="string",  format="date-time", example="2025-03-29 14:30:00"),
 *     @OA\Property(property="expiration_date", type="string", format="date", example="2025-03-29"),
 *     @OA\Property(property="status", type="string", example="Pendiente"),
 *     @OA\Property(property="user", type="string", example="Lucy West")
 * )
 */

    public function listTasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "page" => "required|integer|min:1",
            "per_page" => 'required|integer|min:1|max:1000'
        ]);

        if($validator->fails()){
            return response()->json([
                "error" => true,
                "message" => "Error en validación",
                "message_detail" => $validator->errors()->all()
            ],400);
        }

        //$perPage = $request->input('per_page', 10);

        try {

            $token = $request->header("token");
            $page = $request->input("page");
            $perPage = $request->input("per_page");

            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");

            $user = User::find($userIdLogued);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "Usuario no encontrado",
                    "message_detail" => "No fue posible encontrar al usuario logueado"
                ], 404);
            }

            $tasks = $user->tasks()->with('status')->paginate($perPage);
            return response()->json([
                "error" => false,
                "message" => "Lista de tareas obtenidas correctamente",
                "tasks" => TaskResource::collection($tasks),
                "meta" => [
                    "current_page" => $tasks->currentPage(),
                    "last_page" => $tasks->lastPage(),
                    "per_page" => $tasks->perPage(),
                    "total" => $tasks->total(),
                ]
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


    /**
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     summary="Crear una nueva tarea",
     *     description="Permite a un usuario autenticado crear una nueva tarea. Requiere un token JWT en los headers.",
     *     tags={"Tareas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="header",
     *         required=true,
     *         description="Token JWT del usuario autenticado",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "expiration_date", "status_id"},
     *             @OA\Property(property="title", type="string", example="Revisar documentos"),
     *             @OA\Property(property="description", type="string", example="Revisar y firmar los documentos del cliente"),
     *             @OA\Property(property="expiration_date", type="string", format="date", example="2024-03-15"),
     *             @OA\Property(property="status_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea creada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tarea creada correctamente"),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
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
     *                     "El campo title es obligatorio.",
     *                     "El campo description es obligatorio.",
     *                     "El campo expiration_date es obligatorio.",
     *                     "El campo status_id es obligatorio."
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no autorizado o no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado"),
     *             @OA\Property(property="message_detail", type="string", example="No fue posible encontrar al usuario logueado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Error al guardar tarea"),
     *             @OA\Property(property="message_detail", type="string", example="Detalles del error interno")
     *         )
     *     )
     * )
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
                ], 404);
            }


            $task = new Task();
            $task->title = $request->input("title");
            $task->description = $request->input("description");
            $task->expiration_date = $request->input("expiration_date");
            $task->status_id = $request->input("status_id");
            $task->user_id = $userIdLogued;
            $task->save();

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

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{id}",
     *     summary="Obtiene una tarea especifica",
     *     description="Retorna una tarea especifica asociado al usuario autenticado, requiere un token JWT en los headers.",
     *     tags={"Tareas"},
     *      @OA\Parameter(
     *         name="token",
     *         in="header",
     *         required=true,
     *         description="Token JWT del usuario autenticado",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a obtener",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tarea obtenida correctamente"),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no autorizado o no encontrado o Tarea no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado o Tarea no encontrada"),
     *             @OA\Property(property="message_detail", type="string", example="No fue posible encontrar registros")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Error al obtener la tarea"),
     *             @OA\Property(property="message_detail", type="string", example="Detalles del error interno")
     *         )
     *     )
     * 
     * )
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
                ], 404);
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
    /**
     * @OA\Put(
     *     path="/api/v1/tasks/{id}",
     *     summary="Actualizar una tarea",
     *     description="Permite actualizar una tarea específica de un usuario autenticado.",
     *     tags={"Tareas"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="header",
     *         required=true,
     *         description="Token JWT del usuario autenticado",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos para actualizar la tarea",
     *         @OA\JsonContent(
     *             required={"title", "description", "expiration_date", "status_id"},
     *             @OA\Property(property="title", type="string", example="Actualizar API"),
     *             @OA\Property(property="description", type="string", example="Refactorizar el endpoint de actualización"),
     *             @OA\Property(property="expiration_date", type="string", format="date", example="2024-12-31"),
     *             @OA\Property(property="status_id", type="integer", example=2)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Tarea actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tarea actualizada correctamente"),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     * 
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
     *                     "El campo title es obligatorio.",
     *                     "El campo description es obligatorio.",
     *                     "El campo expiration_date es obligatorio.",
     *                     "El campo status_id es obligatorio."
     *                 }
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no autorizado o no encontrado o Tarea no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado o Tarea no encontrada"),
     *             @OA\Property(property="message_detail", type="string", example="No fue posible encontrar registros")
     *         )
     *     ),
     * 

     * 
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Error al actualizar la tarea"),
     *             @OA\Property(property="message_detail", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
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
                ], 404);
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


    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{id}",
     *     summary="Eliminar una tarea",
     *     description="Elimina una tarea específica asociada al usuario autenticado. Requiere un token JWT en los headers.",
     *     tags={"Tareas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="header",
     *         required=true,
     *         description="Token JWT del usuario autenticado",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea eliminada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tarea eliminada correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no autorizado o no encontrado o Tarea no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado o Tarea no encontrada"),
     *             @OA\Property(property="message_detail", type="string", example="No fue posible encontrar registros")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Error al eliminar la tarea"),
     *             @OA\Property(property="message_detail", type="string", example="Detalles del error interno")
     *         )
     *     )
     * )
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
                ], 404);
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
