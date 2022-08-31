<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testRequiredFieldsForLogin()
    {
        $this->json('POST', 'api/login', ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson([
                "message" => "The email field is required. (and 1 more error)",
                "errors" => [
                    "email" => ["The email field is required."],
                    "password" => ["The password field is required."],
                ]
            ]);
    }

    public function testLoginFailure()
    {
        $loginData = ['email' => 'admin@admin.com', 'password' => 'wrong'];

        $this->json('POST', 'api/login', $loginData, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "status" => "error",
                "message" => "Unauthorized",
            ]);
    }

    public function testSuccessfulLogin()
    {
        $loginData = ['email' => 'admin@admin.com', 'password' => 'password'];

        $this->json('POST', 'api/login', $loginData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                "status",
                "user" => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                "authorisation" => [
                    "token",
                    "type",
                ]
            ]);

        $this->assertAuthenticated();
    }

    public function testTokenRefresh()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->post('api/refresh')
            ->assertStatus(200)
            ->assertJsonStructure([
                "status",
                "user" => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                "authorisation" => [
                    "token",
                    "type",
                ]
            ]);
    }

    public function testSuccessfulLogout()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->post('api/logout')
            ->assertStatus(200)
            ->assertJson([
                "status" => "success",
                "message" => "Successfully logged out",
            ]);
    }
}
