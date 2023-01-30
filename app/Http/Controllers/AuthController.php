<?php

namespace App\Http\Controllers;

use AuthenticationService;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use External\Foo\Exceptions\AuthenticationFailedException;

class AuthController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */

    protected $authenticationService;


    /**
 * AuthController constructor.
 *
 * @param AuthenticationService $authenticationService
 */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

/**
 * @param Request $request
 *
 * @return JsonResponse
 */
    public function login(Request $request):JsonResponse
    {
        $validatedData = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        $login = $validatedData['login'];
        $password = $validatedData['password'];

        try {
            $response = $this->authenticationService->authenticate($login, $password);
            return response()->json(['status' => 'success', 'token' => $response->token]);
        } catch (AuthenticationFailedException $e) {
            return response()->json(['status' => 'failure']);
        } catch (AuthenticationFailedException $e) {
            return response()->json(['error' => 'Invalid login prefix']);
        }
    }

}
