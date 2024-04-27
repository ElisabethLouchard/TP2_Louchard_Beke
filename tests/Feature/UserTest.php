<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use DatabaseMigrations; 

    public function test_update_password_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'name' => 'taytay13',
            'password' => 'cardigan',
            'email' => 'taylorswift@gmail.com',
            'last_name' => 'Taylor',
            'first_name' => 'Swift',
        ]);

        $requestData = [
            'current_password' => 'willow',
            'new_password' => 'fortnight',
            'new_password_confirmation' => 'fortnight'
        ];

        $response = $this->actingAs($user)->postJson('/api/update/' . $user->id(), $requestData);

        $response->assertStatus(FORBIDDEN)
                 ->assertJson(['error' => 'Mot de passe actuel incorrect.']);
    }

    public function test_update_password_with_valid_data()
    {
        $user = User::factory()->create([
            'login' => 'LivPurple17',
            'password' => 'brutal',
            'email' => 'oliviarodriguo@gmail.com',
            'last_name' => 'Rodriguo',
            'first_name' => 'Olivia',
        ]);

        $requestData = [
            'current_password' => 'brutal',
            'new_password' => 'obsessed',
            'new_password_confirmation' => 'obsessed'
        ];

        $response = $this->actingAs($user)->postJson('/api/update/' . $user->id(), $requestData);

        $response->assertStatus(OK)
                 ->assertJson(['message' => 'Mot de passe mis à jour avec succès.']);
    }

    public function test_update_password_unauthenticated_user()
    {
        $requestData = [
            'current_password' => 'sunflower',
            'new_password' => 'fortnight',
            'new_password_confirmation' => 'fortnight'
        ];

        $response = $this->postJson('/api/update/9', $requestData);

        $response->assertStatus(NOT_FOUND);
    }
}
