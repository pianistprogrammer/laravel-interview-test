<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\AuthWS;
use App\LoginService;
use App\Authenticator;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testFooLogin()
    {
        $request = new Request();
        $request->merge([
            'login' => 'FOO123',
            'password' => 'foo-bar-baz',
        ]);

        $response = $this->post('/login', $request->all());

        $response->assertJson([
            'status' => 'success',
            'token' => 'string'
        ]);

        $this->mock(AuthWS::class)
            ->shouldReceive('authenticate')
            ->once()
            ->andReturn(true);
    }
}
