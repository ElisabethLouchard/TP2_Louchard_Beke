<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Film;
use Laravel\Sanctum\Sanctum;

class CriticTest extends TestCase
{
    use DatabaseMigrations;

    public function test_create_critic_already_exists()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $film = Film::factory()->create();

        $requestData = [
            'score' => 2,
            'comment' => 'Mauvais',
        ];

        $response = $this->postJson('/api/critics/'. $film->id, $requestData);
        $response->assertStatus(CREATED);
        
        $response2 = $this->postJson('/api/critics/'. $film->id, $requestData);

        $response2->assertStatus(FORBIDDEN)
         ->assertJson(['error' => 'Vous avez déjà critiqué ce film.']);
    }

    public function test_create_critic_successfully()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $film = Film::factory()->create();

        $requestData = [
            'score' => 4,
            'comment' => 'Excellent!',
        ];

        $response = $this->postJson('/api/critics/'. $film->id, $requestData);

        $response->assertStatus(CREATED);
    }

    public function test_throttling_on_critic_creation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $film = Film::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $requestData = [
                'score' => $i + 1,
                'comment' => 'trop bon ' . ($i + 1),
            ];

            $response = $this->postJson('/api/critics/'. $film->id, $requestData);
        }

        $requestData = [
            'score' => 61,
            'comment' => 'yeah yeah',
        ];
        $response = $this->postJson('/api/critics/'. $film->id, $requestData);

        $response->assertStatus(HTTP_TOO_MANY_REQUESTS)
                ->assertJson(['message' => 'Too Many Attempts.']);
    }
   
}
