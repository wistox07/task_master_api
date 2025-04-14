<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
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

        User::factory()->create([
            "name" => "admin",
            "email" => "admin@gmail.com",
            "password" => Hash::make('12345678')
        ])->each(function ($user){
            Task::factory(10)->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);
        });
        
    

        User::factory(10)->create()->each(function ($user){
            Task::factory()->create([
                "user_id" => $user->id,
                "status_id" => Status::inRandomOrder()->first()->id
            ]);
        });

    }
}
