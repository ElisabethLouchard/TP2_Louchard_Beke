<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Film;
use App\Models\Critic;
use App\Models\Language;

class CriticTest extends TestCase
{
    use DatabaseMigrations;

    public function test_create_critic_already_exists()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $requestData = [
            'film_id' => 1,
            'score' => 2,
            'comment' => 'Mauvais',
        ];

        $requestData2 = [
            'film_id' => 2,
            'score' => 1,
            'comment' => 'Ewww',
        ];

        $response = $this->postJson('/api/critics', $requestData);
        $response->assertStatus(CREATED);
        
        $response2 = $this->postJson('/api/critics', $requestData2);

        $response2->assertStatus(FORBIDDEN)
                ->assertJson(['error' => 'Vous avez déjà critiqué un film.']);
    }

    public function test_create_critic_successfully()
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
   
}
