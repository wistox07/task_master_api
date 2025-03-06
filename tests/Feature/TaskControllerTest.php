<?php

namespace Tests\Feature;

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;


    /**
     * A basic feature test example.
     *
     * @return void
     */
    
        //OBTENER TODAS LAS TAREAS
        public function test_end_point_obtiene_todas_tareas_falla_en_validacion(){

            $response = $this->get('/api/v1/tasks/me');
    
            $response->assertStatus(400)
            ->assertJson([
                "error" => true,
            ]);
        }

        public function test_end_point_obtiene_todas_tareas_falla_en_usuario_inexistente(){

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);

            $token = JWTAuth::fromUser($user);

            $user->delete(); // Lo elimina de la base de datos

            $response = $this->withHeaders([
                'token' => $token
            ])->get('/api/v1/tasks/me');


            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);
        }

        public function test_end_point_obtiene_todas_tareas_correcto(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);



            $token = JWTAuth::fromUser($user);

            $response = $this->withHeaders([
                'token' => $token
            ])->get('/api/v1/tasks/me');


            $response->assertStatus(200)
                     ->assertJsonStructure(['tasks'])
                     ->assertJson([
                       "error" => false,
            ]);
       }


       //REGISTRAR TAREA
        public function test_end_point_registrar_tareas_falla_en_validacion(){

            $response = $this->postJson('/api/v1/tasks');

            $response->assertStatus(400)
            ->assertJson([
                "error" => true,
            ]);
        }

        public function test_end_point_registrar_tarea_falla_en_usuario_inexistente(){
            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);

            $token = JWTAuth::fromUser($user);

            $user->delete(); // Lo elimina de la base de datos

            $response = $this->withHeaders([
                'token' => $token
            ])->postJson('/api/v1/tasks',[
                "title" => "prueba",
                "description" => "prueba",
                "expiration_date" => "2025-01-01",
                "status_id" => Status::inRandomOrder()->first()->id
            ]);


            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);
        }

        public function test_end_point_registrar_tarea_correcto(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);
           

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);

            $token = JWTAuth::fromUser($user);

            $response = $this->withHeaders([
                'token' => $token
            ])->postJson('/api/v1/tasks',[
                "title" => "prueba",
                "description" => "prueba",
                "expiration_date" => "2025-01-01",
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $response->assertStatus(200)
            ->assertJsonStructure(['task'])
            ->assertJson([
              "error" => false,
          ]);
          
        }


        //OBTIENE TAREA EN  ESPECIFICO
        public function test_end_point_obtener_tarea_falla_en_validacion(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $response = $this->get("/api/v1/tasks/$taskId");

            $response->assertStatus(400)
            ->assertJson([
                "error" => true,
            ]);
        }

        public function test_end_point_obtener_tarea_falla_en_usuario_inexistente(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $user->delete();

            $response = $this->withHeaders([
                'token' => $token
            ])->get("/api/v1/tasks/$taskId");


            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);
        }

        public function test_end_point_obtener_tarea_correcto(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $response = $this->withHeaders([
                'token' => $token
            ])->get("/api/v1/tasks/$taskId");


            $response->assertStatus(200)
            ->assertJsonStructure(['task'])
            ->assertJson([
              "error" => false,
          ]);
        }

        //ACTUALIZA UNA TAREA EN ESPECIFICO
        public function test_end_point_actualizar_tarea_falla_en_validacion(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $response = $this->put("/api/v1/tasks/$taskId",[
                "title" => "prueba",
                "description" => "prueba",
                "expiration_date" => "2025-01-01",
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $response->assertStatus(400)
            ->assertJson([
                "error" => true,
            ]);
        }
        
        public function test_end_point_actualizar_tarea_falla_en_usuario_inexistente(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $user->delete();

            $response = $this->withHeaders([
                'token' => $token
            ])->put("/api/v1/tasks/$taskId",[
                "title" => "prueba",
                "description" => "prueba",
                "expiration_date" => "2025-01-01",
                "status_id" => Status::inRandomOrder()->first()->id
            ]);


            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);
        }
        
        public function test_end_point_actualizar_tarea_correcto(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $response = $this->withHeaders([
                'token' => $token
            ])->put("/api/v1/tasks/$taskId",[
                "title" => "prueba",
                "description" => "prueba",
                "expiration_date" => "2025-01-01",
                "status_id" => Status::inRandomOrder()->first()->id
            ]);


            $response->assertStatus(200)
            ->assertJsonStructure(['task'])
            ->assertJson([
              "error" => false,
          ]);
        }

        //ELIMINA TAREA EN  ESPECIFICO
        public function test_end_point_eliminar_tarea_falla_en_validacion(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $response = $this->delete("/api/v1/tasks/$taskId");

            $response->assertStatus(400)
            ->assertJson([
                "error" => true,
            ]);
        }
        
        public function test_end_point_eliminar_tarea_falla_en_usuario_inexistente(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $user->delete();

            $response = $this->withHeaders([
                'token' => $token
            ])->delete("/api/v1/tasks/$taskId");


            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);
        }
        

        public function test_end_point_eliminar_tarea_correcto(){

            Status::factory()->createMany([
                [
                    "name" => "Pendiente",
                    "description" => "Estado Pendiente",
                    "identifier_code" => "pending_status"
                ],
                [
                    "name" => "Completada",
                    "description" => "Estado Completada",
                    "identifier_code" => "completed_status"
    
                ]
            ]);

            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);


            $tasks = Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);

            $taskId = $tasks->first()->id; 
            $token = JWTAuth::fromUser($user);

            $response = $this->withHeaders([
                'token' => $token
            ])->delete("/api/v1/tasks/$taskId");


            $response->assertStatus(200)
            ->assertJson([
                "error" => false,
            ]);
        }

}
