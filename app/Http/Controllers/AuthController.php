<?php

namespace App\Http\Controllers;

use Lcobucci\JWT\Builder;
use Illuminate\Http\Request;
use External\Foo\Auth\AuthWS;
use External\Bar\Auth\LoginService;
use External\Baz\Auth\Authenticator;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use External\Baz\Auth\Responses\Failure;
use Symfony\Component\HttpFoundation\JsonResponse;
use External\Foo\Exceptions\AuthenticationFailedException;

class AuthController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request):JsonResponse {
        $validatedData = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        $login = $validatedData['login'];
        $password = $validatedData['password'];

        $prefix = substr($login, 0, 3);

        $signer = new Sha256();
        $secret = getenv('JWT_SECRET');
        $time = time();
        $token = (new Builder())
            ->setIssuedAt($time)
            ->setExpiration($time + 3600)
            ->set('login', $login)
            ->set('system', $prefix)
            ->sign($signer, $secret)
            ->getToken();

        if($prefix === 'FOO') {
            try {
               AuthWS::authenticate($login, $password);
               return reponse()->json(['status' => 'success', 'token'=> (string) $token]);
               
            } catch (AuthenticationFailedException $e) {
               return reponse()->json(['status' => 'failure']);
            }
        } else if($prefix === 'BAR') {
            $response = LoginService::login($login, $password);
            if ($response){
                return reponse()->json(['status' => 'success', 'token'=> (string) $token]);
            }
           else {
            return reponse()->json(['status' => 'failure']);
            }
        } else if($prefix === 'BAZ') {
            $response = Authenticator::auth($login, $password);

            if($response instanceof Failure) {
                return reponse()->json(['status' => 'failure']);
            }
            else{
                return reponse()->json(['status' => 'success', 'token'=> (string) $token]);
            }
        } else {
            return response()->json(['error' => 'Invalid login prefix']);
        }
    }

}
