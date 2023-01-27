<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\LoginService;
use App\Authenticator;
use Illuminate\Http\Request;
use External\Foo\Auth\AuthWS;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    public function testFooLoginFailure()
    {
        $request = new Request();
        $request->merge([
        'login' => 'FOO123',
        'password' => 'wrong-password',
        ]);
        $response = $this->post('/login', $request->all());

        $response->assertJson([
            'status' => 'failure',
            'error' => 'Invalid login or password'
        ]);

        $this->mock(AuthWS::class)
            ->shouldReceive('authenticate')
            ->once()
            ->andReturn(false);
    }

}
