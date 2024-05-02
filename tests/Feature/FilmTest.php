<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Film;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class FilmTest extends TestCase
{
    use DatabaseMigrations;

    public function test_update_movie_by_admin()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        Sanctum::actingAs($admin);

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
        $user = User::factory()->create(['role_id' => 1]);
        Sanctum::actingAs($user);

        $movie = Film::factory()->create();

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
        $admin = User::factory()->create(['role_id' => 2]);
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

        $response->assertStatus(NOT_FOUND);
    }

    public function test_update_nonexistent_movie_throttling()
    {
        $admin = User::factory()->create(['role_id' => 2]);
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
    
    //Tests pour le create
    /*public function testPostFilmShouldReturn201WhenCreated()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 2;

        $json = [ 
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        $response = $this->postJson('/api/films', $json);
        $response->assertStatus(CREATED);
        $response->assertJsonFragment($json);
    }
    
    public function testPostFilmShouldReturn401WhenNotAuthenticated()
    {
        $json = [ 
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        $error_message = ['message' => "Unauthenticated."];
        $response = $this->postJson('/api/films', $json);
        $response->assertStatus(UNAUTHORIZED);
        $response->assertJsonFragment($error_message);
    }

    
    public function testPostFilmShouldReturn403WhenForbidden()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $json = [ 
        "title"=> "First Love", 
        "release_year"=> 2023,
        "length"=> 6,
        "description"=> "passionate",
        "rating"=> 56,
        "language_id"=> 1,
        "special_features"=> "special person",
        "image"=> "love of my life"
        ];
        
        $error_message = ['message' => "L'utilsateur n'a pas les permissions pour créer un film"];

        $response = $this->postJson('/api/films', $json);
        $response->assertStatus(FORBIDDEN);
        $response->assertJson($error_message);
    }

    
    public function testPostFilmShouldReturn422WhenInvalidData()
    {
        $user = User::create([
        "login" => "login",
        "password" => bcrypt("blablabla"),
        "email" => "aya@gmail.com",
        "last_name" => "nakamura",
        "first_name"=> "aya"
        ]);

        Sanctum::actingAs($user , ['*']);

        $user->role_id = 2;

        $json = [ 
        "title"=> "First Love", 
        "release_year"=> 2023,
        "length"=> 6,
        "description"=> "passionate",
        "rating"=> 56,
        "language_id"=> 1
        ];

        $response = $this->postJson('/api/films', $json);
        $response->assertStatus(INVALID_DATA);
    }

    
    public function testPostFilmShouldReturn429WhenTooManyRequest()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 2;

        $json = [ 
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        for($i = 0; $i<60; $i++)
        {
            $response = $this->postJson('/api/films', $json);
            $response->assertStatus(CREATED);
        }

        $response = $this->postJson('/api/films', $json);
        $response->assertStatus(HTTP_TOO_MANY_REQUESTS);
    }

    
    //Tests pour le delete
    public function testDeleteFilmShouldReturn204WhenDeleted()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 2;
        $json = [ 
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];
        $this->postJson('/api/films', $json);

        $response = $this->delete('/api/films/' . $user->id);

        $response->assertStatus(NO_CONTENT);
    }

    public function testDeleteFilmShouldReturn401WhenNotAuthenticated()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 2;
        $json = [
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        $filmToDelete = $this->postJson('/api/films', $json);
        //$filmToDelete->id = 1;
        $this->getJson('/api/signout');
        $this->assertEmpty($user->tokens);
        //erreur à modifier
        //$response = $this->delete('/api/films/' . $filmToDelete->id);

        //$response->assertStatus(UNAUTHORIZED);
    }

    public function testDeleteFilmShouldReturn403WhenForbidden()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 1;
        $json = [
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        $filmToDelete = $this->postJson('/api/films', $json);
        //$filmToDelete->id = 1;
        $response = $this->delete('/api/films/' . $filmToDelete->id);
        
        $error_message = ['message' => "L'utilsateur n'a pas les permissions pour supprimer ce film"];
        $response->assertStatus(FORBIDDEN);
        $response->assertJson($error_message);
    }

    public function testDeleteFilmShouldReturn429WhenTooManyRequest()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 2;
        $json = [
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        $filmToDelete = $this->postJson('/api/films', $json);
        //$filmToDelete->id = 1;
        for($i = 0; $i< 60 ; $i++)
        {
            $response = $this->delete('/api/films/' . $filmToDelete->id);
        }
        $response = $this->delete('/api/films/' . $filmToDelete->id);
        $response->assertStatus(HTTP_TOO_MANY_REQUESTS);
    }

    public function testDeleteFilmShouldReturn404WhenFilmNotFound()
    {
        $user = User::create([
            "login" => "login",
            "password" => bcrypt("blablabla"),
            "email" => "aya@gmail.com",
            "last_name" => "nakamura",
            "first_name"=> "aya"
        ]);
        Sanctum::actingAs($user, ['*']);
        $user->role_id = 2;
        $json = [
            "title"=> "First Love", 
            "release_year"=> 2023,
            "length"=> 6,
            "description"=> "passionate",
            "rating"=> 56,
            "language_id"=> 1,
            "special_features"=> "special person",
            "image"=> "love of my life"
        ];

        $filmToDelete = $this->postJson('/api/films', $json);
        //$filmToDelete->id = 1;
        $response = $this->delete('/api/films/' . $filmToDelete->id*2);
        $response->assertStatus(NOT_FOUND);
    }*/

}
