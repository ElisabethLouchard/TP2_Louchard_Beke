<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Language;
use App\Models\Film;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class FilmTest extends TestCase
{
   use DatabaseMigrations;

    //Tests pour le create
    public function testPostFilmShouldReturn201WhenCreated()
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

        $response = $this->postJson('/api/films', $json);
        $response->assertStatus(UNAUTHORIZED);
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
        //$response->assertJson($json);
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
        //$response->assertJsonFragment($json);
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
        //$response->assertJsonFragment($json);

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
        $filmToDelete->id = 1;
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
        $filmToDelete->id = 1;
        $response = $this->delete('/api/films/' . $filmToDelete->id);

        $response->assertStatus(FORBIDDEN);
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
        $filmToDelete->id = 1;
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
        $filmToDelete->id = 1;
        $response = $this->delete('/api/films/' . $filmToDelete->id*2);
        $response->assertStatus(NOT_FOUND);
    }

}
