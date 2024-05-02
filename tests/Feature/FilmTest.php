<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Film;
use Tests\TestCase;
use App\Models\User;

class FilmTest extends TestCase
{
    use DatabaseMigrations;

    public function test_update_movie_by_admin()
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $this->actingAs($admin);

        $movie = Film::factory()->create([
            'title' => 'Raiponces',
            'release_year' => 2010,
            'length' => 100,
            'description' => 'Princesse aux cheveux dorés',
            'rating' => 'G',
            'language_id' => 1,
            'special_features' => 'Deleted Scenes,Commentaries',
            'image' => 'image.jpg',
        ]);

        $requestData = [
            'title' => 'Raiponce',
            'release_year' => 2010,
            'length' => 100,
            'description' => 'Princesse aux cheveux dorés',
            'rating' => 'G',
            'language_id' => 1,
            'special_features' => 'Deleted Scenes,Commentaries',
            'image' => 'image.jpg',
        ];

        $response = $this->putJson('/api/films/' . $movie->id, $requestData);

        $response->assertStatus(OK)
                ->assertJson(['title' => 'Raiponce']);
    }

    public function test_update_movie_by_non_admin()
    {
        $user = User::factory()->create(['role_id' => 2]);
        $this->actingAs($user);

        $movie = Film::factory()->create([
            $requestData = [
                'title' => 'Monster Incorp',
                'release_year' => 2001,
                'length' => 90,
                'description' => 'Sully et Mike',
                'rating' => 'G',
                'language_id' => 1,
                'special_features' => 'Deleted Scenes,Commentaries',
                'image' => 'image.jpg',
            ]
        ]);

        $requestData = [
            'title' => 'Monster Inc',
            'release_year' => 2001,
            'length' => 90,
            'description' => 'Sully et Mike',
            'rating' => 'G',
            'language_id' => 1,
            'special_features' => 'Deleted Scenes,Commentaries',
            'image' => 'image.jpg',
        ];

        $response = $this->putJson('/api/films/' . $movie->id, $requestData);

        $response->assertStatus(FORBIDDEN)
                ->assertJson(['error' => 'Vous n\'avez pas les autorisations nécessaires pour cette action.']);
    }

    public function test_update_nonexistent_movie()
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $this->actingAs($admin);

        $nonExistentMovieId = 9999;

        $requestData = [
            'title' => 'Cars',
            'release_year' => 2006,
            'length' => 120,
            'description' => 'Kashow',
            'rating' => 'G',
            'language_id' => 1,
            'special_features' => ['Deleted Scenes', 'Commentaries'],
            'image' => 'nouvelle_image.jpg',
        ];

        $response = $this->putJson('/api/films/' . $nonExistentMovieId, $requestData);

        $response->assertStatus(NOT_FOUND)
                ->assertJson(['error' => 'Le film n\'existe pas.']);
    }

    public function test_update_nonexistent_movie_throttling()
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $this->actingAs($admin);

        $nonExistentMovieId = 9999;

        $requestData = [
            'title' => 'Cars',
            'release_year' => 2006,
            'length' => 120,
            'description' => 'Kashow',
            'rating' => 'G',
            'language_id' => 1,
            'special_features' => ['Deleted Scenes', 'Commentaries'],
            'image' => 'nouvelle_image.jpg',
        ];

        for ($i = 0; $i < 60; $i++) {
            $response = $this->putJson('/api/films/' . $nonExistentMovieId, $requestData);
        }

        $response = $this->putJson('/api/films/' . $nonExistentMovieId, $requestData);

        $response->assertStatus(HTTP_TOO_MANY_REQUESTS)
                ->assertJson(['message' => 'Too Many Attempts.']);
    }
    
}
