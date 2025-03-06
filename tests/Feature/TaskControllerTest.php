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
                "status_id" => 1
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
                "status_id" => 1
            ]);


            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);
        }

        
        /*
         public function test_login_falla_en_usuario_inexistente(){
    
            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);
    
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test1@test.com',
                'password' => '12345678',
            ]);     
    
            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);;
    
        }
    
        public function test_login_falla_en_password_incorrecto(){
    
            $user = User::factory()->create([
                "email" => 'test@test.com',
                "name" => "test",
                "password" => Hash::make("12345678"),
            ]);
    
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test1@test.com',
                'password' => '123456789',
            ]);     
    
            $response->assertStatus(404)
            ->assertJson([
                "error" => true,
            ]);;
    
        }
    
    
        public function test_login_correcto(){
             $user = User::factory()->create([
                 "email" => 'test@test.com',
                 "name" => "test",
                 "password" => Hash::make("12345678"),
             ]);
     
             $response = $this->postJson('/api/v1/auth/login', [
                 'email' => 'test@test.com',
                 'password' => '12345678',
             ]);
     
             $response->assertStatus(200)
                      ->assertJsonStructure(['token'])
                      ->assertJsonStructure(['user'])
                      ->assertJson([
                        "error" => false,
                    ]);;
        }
                    */

}
