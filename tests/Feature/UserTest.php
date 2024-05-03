<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use DatabaseMigrations; 

    public function test_update_password_with_valid_data()
    {
        $user = User::factory()->create();
    
        Sanctum::actingAs($user);
    
        $requestData = [
            'new_password' => 'cardigan',
            'new_password_confirmation' => 'cardigan'
        ];
    
        $response = $this->patchJson('/api/users/' . $user->id, $requestData);
    
        $response->assertStatus(OK)
                 ->assertJson(['message' => 'Mot de passe mis à jour avec succès.']);
    }

    public function test_update_password_unauthenticated_user()
    {
        $requestData = [
            'new_password' => 'fortnight',
            'new_password_confirmation' => 'fortnight'
        ];

        $response = $this->patchJson('/api/users/1', $requestData);

        $response->assertStatus(UNAUTHORIZED);
    }

   public function test_update_password_with_wrong_confirmation_password()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $requestData = [
            'new_password' => 'obsessed',
            'new_password_confirmation' => 'obsess'
        ];

        $response = $this->patchJson('/api/users/' . $user->id, $requestData);

        $response->assertStatus(INVALID_DATA);
    }

    public function test_update_password_with_user_non_existent()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $requestData = [
            'new_password' => 'obsessed',
            'new_password_confirmation' => 'obsessed'
        ];

        $response = $this->patchJson('/api/users/9', $requestData);

        $response->assertStatus(NOT_FOUND);
    }

    public function test_throttling_on_user_creation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 60; $i++) {
            $requestData = [
                'new_password' => $i + 1,
                'new_password_confirmation' => $i + 1,
            ];

            $response = $this->patchJson('/api/users/'. $user->id, $requestData);
        }

        $requestData = [
            'new_password' => 'yeah yeah',
            'new_password_confirmation' => 'yeah yeah',
        ];
        $response = $this->patchJson('/api/users/'. $user->id, $requestData);

        $response->assertStatus(HTTP_TOO_MANY_REQUESTS)
                ->assertJson(['message' => 'Too Many Attempts.']);
    }

    // Test getUser
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
