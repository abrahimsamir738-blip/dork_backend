<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
 use WithoutModelEvents;

 /**
  * Seed the application's database.
  */
 public function run(): void
 {
  // Seed doctors
  $this->call([
   DoctorSeeder::class,
  ]);

  //Optionally seed users
  User::factory()->create([
   'name' => 'Test User',
   'email' => 'test@example.com',
   'password' => '12345678',
  ]);
 }
}
