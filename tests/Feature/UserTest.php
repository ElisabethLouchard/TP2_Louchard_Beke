<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;



class UserTest extends TestCase
{
    use DatabaseMigrations; 

    public function testGetUserShouldReturn200WhenOk()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->id = 1;
        $userInfo = 
        [ "login" => "login",
        "email" => "aya@gmail.com",
        "last_name" => "nakamura",
        "first_name"=> "aya"];

        $response = $this->getJson('/api/users/' . $user->id);
        $response->assertStatus(OK);
        $response->assertJsonFragment($userInfo);
    }

   
    public function testGetUserShouldReturn401WhenNotAuthenticated()
    {
        
        $response = $this->getJson('/api/users/1');

        $error_message = ['message' => "Unauthenticated."];
        $response->assertStatus(UNAUTHORIZED);
        $response->assertJsonFragment($error_message);
    }

    
    public function testGetUserShouldReturn403WhenForbidden()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->id = 1;

        $error_message = ['message' => "L'utilsateur n'a pas les permissions pour afficher cet utilisateur"];
        $response = $this->getJson('/api/users/' . $user->id *2);
        $response->assertStatus(FORBIDDEN);
        $response->assertJsonFragment($error_message);
    }

    public function testGetUserShouldReturn429WhenTooManyRequest()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->id = 1;

        for($i = 0; $i<60 ; $i++)
        {
            $response = $this->getJson('/api/users/' . $user->id);
            $response->assertStatus(OK);
        }
        $response = $this->getJson('/api/users/' . $user->id);
        $response->assertStatus(HTTP_TOO_MANY_REQUESTS);
    }

}
