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
        $response->assertStatus(UNAUTHORIZED);
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

        $response = $this->getJson('/api/users/' . $user->id *2);
        $response->assertStatus(FORBIDDEN);
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
