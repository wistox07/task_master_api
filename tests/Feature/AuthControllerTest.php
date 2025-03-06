<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */


    //LOGIN
    public function test_end_point_login_falla_en_validacion(){

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(400)
        ->assertJson([
            "error" => true,
        ]);
    }

     public function test_end_point_login_falla_en_usuario_inexistente(){

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

    public function test_end_point_login_falla_en_password_incorrecto(){

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


    public function test_end_point_login_correcto(){
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

     
     //REGISTER
     public function test_end_point_register_falla_en_validacion(){

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => '',
            'name' => '',
            'password' => '',
             
        ]);

        $response->assertStatus(400)
        ->assertJson([
            "error" => true,
        ]);
     }

     public function test_register_correcto(){
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'test@test.com',
            'password' => '12345678',
            'name' => "test"
        ]);

        $response->assertStatus(200)
        ->assertJsonStructure(['user'])
        ->assertJson([
          "error" => false,
      ]);;
    }
}
