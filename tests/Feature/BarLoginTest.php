<?php

namespace Tests\Feature;

use Mockery;
use App\AuthWS;
use Tests\TestCase;
use App\Authenticator;
use Illuminate\Http\Request;
use External\Bar\Auth\LoginService;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // set the JWT_SECRET environment variable
        putenv('JWT_SECRET=secret');
    }

   public function testBarLoginSuccess()
{
    // Arrange
    $login = 'BAR12345';
    $password = 'foo-bar-baz';
    $expectedResponse = ['status' => 'success', 'token' => 'valid_token'];
    $mock = Mockery::mock(LoginService::class);
    $mock->shouldReceive('login')->with($login, $password)->andReturn(true);
    $this->app->instance(LoginService::class, $mock);

    // Act
    $response = $this->postJson('/login', [
        'login' => $login,
        'password' => $password
    ]);

    // Assert
    $response->assertStatus(200);
    $response->assertJson($expectedResponse);
}


}