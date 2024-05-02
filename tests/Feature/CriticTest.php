<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CriticTest extends TestCase
{
    use DatabaseMigrations;

    public function test_create_critic_already_exists()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $requestData = [
            'score' => 2,
            'comment' => 'Mauvais',
        ];

        $requestData2 = [
            'score' => 1,
            'comment' => 'Ewww',
        ];

        $response = $this->postJson('/api/critics', $requestData);
        $response->assertStatus(201);
        
        $response2 = $this->postJson('/api/critics', $requestData2);

        $response2->assertStatus(403)
                ->assertJson(['error' => 'Vous avez déjà critiqué un film.']);
    }

    /*public function test_create_critic_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $requestData = [
            'film_id' => 1, 
            'score' => 4,
            'comment' => 'Excellent!',
        ];

        $response = $this->postJson('/api/critics', $requestData);

        $response->assertStatus(CREATED)
                ->assertJsonStructure(['id', 'user_id', 'film_id', 'score', 'comment', 'created_at', 'updated_at']);
    }

    public function test_throttling_on_critic_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 60; $i++) {
            $requestData = [
                'score' => $i + 1,
                'comment' => 'trop bon ' . ($i + 1),
            ];

            $response = $this->postJson('/api/critics', $requestData);
        }

        $requestData = [
            'score' => 61,
            'comment' => 'yeah yeah',
        ];
        $response = $this->postJson('/api/critics', $requestData);

        $response->assertStatus(HTTP_TOO_MANY_REQUESTS)
                ->assertJson(['message' => 'Too Many Attempts.']);
    }*/
   
}
