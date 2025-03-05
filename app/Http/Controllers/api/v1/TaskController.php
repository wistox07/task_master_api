<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listTasks(Request $request)
    {
        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");
    
            $user = User::find($userIdLogued);
            if(!$user){
                 throw new Exception("Usuario no encontrado");
            }

            $tasks = $user->tasks()->with('status')->get();
            return response()->json([
                "error" => false,
                "data" => $tasks
            ]); 

        }catch(Throwable $ex){
            return response()->json([
                "error" => true,
                "message" => "Se presentó un problema en el proceso de listar tareas",
                "message_detail" => $ex->getMessage()
            ],500);
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
        $task = Task::create($request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean'
        ]));
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request , $id)
    {
       
        try {

            $token = $request->header("token");
            $payload = JWTAuth::setToken($token)->getPayload();
            $userIdLogued = $payload->get("sub");
    
            $user = User::find($userIdLogued);
            if(!$user){
                 throw new Exception("Usuario no encontrado");
            }

            $task = $user->tasks()->with('status')->find($id);
            if(!$task){
                throw new Exception("Tarea no encontrada");
            }
            return response()->json([
                "error" => false,
                "data" => $task
            ]); 

        }catch(Throwable $ex){
            return response()->json([
                "error" => true,
                "message" => "Se presentó un problema en el proceso de listar la tarea",
                "message_detail" => $ex->getMessage()
            ],500);
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
        return response()->json($id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json($id);

    }
}
