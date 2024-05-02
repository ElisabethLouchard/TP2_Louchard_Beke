<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use DatabaseMigrations; 

    public function test_update_password_with_valid_data()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $requestData = [
            'new_password' => 'obsessed',
            'new_password_confirmation' => 'obsessed'
        ];

        $response = $this->patchJson('/api/users/' . $user->id, $requestData);

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

        $response = $this->patchJson('/api/users/1', $requestData);

        $response->assertStatus(UNAUTHORIZED);
    }

    public function test_update_password_with_wrong_confirmation_password()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $requestData = [
            'new_password' => 'obsessed',
            'new_password_confirmation' => 'obsess'
        ];

        $response = $this->patchJson('/api/users/' . $user->id, $requestData);

        $response->assertStatus(FORBIDDEN)
                 ->assertJson(['error' => 'Vous n\'avez pas les autorisations nécessaires pour cette action.']);
    }

    public function test_update_password_with_user_non_existent()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $requestData = [
            'new_password' => 'obsessed',
            'new_password_confirmation' => 'obsessed'
        ];

        $response = $this->patchJson('/api/users/9', $requestData);

        $response->assertStatus(NOT_FOUND);
                 //->assertJson(['error' => 'Utilisateur non trouvé.']);
    }

    public function test_throttling_on_film_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 60; $i++) {
            $requestData = [
                'new_password' => $i + 1,
                'new_password_confirmation' => $i + 1,
            ];

            $response = $this->postJson('/api/critics', $requestData);
        }

        $requestData = [
            'new_password' => 61,
            'new_password_confirmation' => 'yeah yeah',
        ];
        $response = $this->postJson('/api/films', $requestData);

        $response->assertStatus(HTTP_TOO_MANY_REQUESTS)
                ->assertJson(['message' => 'Too Many Attempts.']);
    }
}
