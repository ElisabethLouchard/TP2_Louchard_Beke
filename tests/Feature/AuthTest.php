<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
define('HTTP_TOO_MANY_REQUESTS', 429);
define('LIMIT_THROTTLING', 5);

class AuthTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_signup_throttling()
    {   
        Sanctum::actingAs(User::factory()->create(), ['*']);

        for ($i = 0; $i < LIMIT_THROTTLING; $i++) {
            $response = $this->postJson('/api/signup', [
                'login' => 'LivPurple17',
                'password' => 'brutal',
                'email' => 'oliviarodriguo'.strval($i).'@gmail.com',
                'last_name' => 'Rodriguo',
                'first_name' => 'Olivia',
            ]);
            $response->assertStatus(CREATED);
        }

        $response = $this->postJson('/api/signup', [
                'login' => 'BilEil2001',
                'password' => 'duh',
                'email' => 'billieeillish6@gmail.com',
                'last_name' => 'Billie',
                'first_name' => 'Eillish',
        ]);
        $response->assertStatus(HTTP_TOO_MANY_REQUESTS);
    }

    public function test_signin_throttling()
    {   
        $user = User::factory()->create();

        for ($i = 0; $i < 6; $i++) { 
            $response = $this->postJson('/api/signin', [
                'login' => $user->email, 
                'password' => 'notit', 
            ]);

            if ($i < 5) {
                $response->assertStatus(UNAUTHORIZED); 
            } else {
                $response->assertStatus(HTTP_TOO_MANY_REQUESTS); 
            }
        }
    }

    public function test_signout_throttling()
    {   
        Sanctum::actingAs(User::factory()->create(), ['*']);

        for ($i = 0; $i < 6; $i++) { 
            $response = $this->getJson('/api/signout');

            if ($i < 5) {
                $response->assertStatus(NO_CONTENT);
            } else {
                $response->assertStatus(HTTP_TOO_MANY_REQUESTS); 
            }
        }
    }

    public function test_register_incomplete_data()
    {
        $userData = [
            'login' => 'taytay13',
            'password' => 'cardigan',
            'email' => 'taylorswift@gmail.com',
        ];

        $response = $this->postJson('/api/signup', $userData);
        $response->assertStatus(BAD_REQUEST)
                ->assertJson([
                    'error' => true,
                ]);
    }

    public function test_register_email_already_use()
    {
        $userData = [
            'name' => 'taytay13',
            'password' => 'cardigan',
            'email' => 'taylorswift@gmail.com',
            'last_name' => 'Taylor',
            'first_name' => 'Swift',
        ];

        $secondUserData = [
            'name' => 'taytay13',
            'password' => 'cardigan',
            'email' => 'taylorswift@gmail.com',
            'last_name' => 'Taylor',
            'first_name' => 'Swift',
        ];

        $this->postJson('/api/signup', $userData);
        $response = $this->postJson('/api/signup', $secondUserData);
        $response->assertStatus(BAD_REQUEST)
                ->assertJson([
                    'error' => true,
                ]);
    }

    public function test_register_invalid()
    {
        $userData = [
            'name' => 'taytay13',
            'password' => 'cardigan',
            'email' => 'taylorswift',
            'last_name' => 'Taylor',
            'first_name' => 'Swift',
        ];

        $this->postJson('/api/signup', $userData);
        $response = $this->postJson('/api/signup', $userData);
        $response->assertStatus(BAD_REQUEST)
                ->assertJson([
                    'error' => true,
                ]);
    }

    public function test_register_success()
    {
        $userData = [
            'login' => 'PostMalone123',
            'password' => 'sunflower',
            'email' => 'postmal@gmail.com',
            'last_name' => 'Malone',
            'first_name' => 'Post',
        ];
    
        $response = $this->postJson('/api/signup', $userData);
    
        $response->assertStatus(CREATED);
        $response->assertJson([
            'user' => [
                'login' => 'PostMalone123',
                'email' => 'postmal@gmail.com',
                'last_name' => 'Malone',
                'first_name' => 'Post',
            ]
        ]);
    }    

    public function test_signin_failure()
    {
        $loginData = [
            'login' => 'jonasBrother3',
            'password' => 'waffle',
        ];

        $response = $this->postJson('/api/signin', $loginData);
        $response->assertStatus(UNAUTHORIZED);
    }

    public function test_signin_success()
    {
        $user = User::factory()->create([
            'login' => 'Riri3',
            'password' => bcrypt('umbrella'),
        ]);

        $response = $this->postJson('/api/signin', [
            'login' => 'Riri3', 
            'password' => 'umbrella',
        ]);

        $response->assertStatus(CREATED)
                 ->assertJsonStructure(['token']);

        $responseData = $response->json();
        $this->assertArrayHasKey('token', $responseData);
    }

    public function test_signout_success()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/signout', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(NO_CONTENT);

        $this->assertEmpty($user->tokens);
    }

    public function test_signout_failure()
    {
        $response = $this->getJson('/api/signout', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(UNAUTHORIZED)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }
}
