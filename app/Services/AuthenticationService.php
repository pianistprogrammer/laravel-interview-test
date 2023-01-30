<?php

use External\Foo\Auth\AuthWS;
use External\Bar\Auth\LoginService;
use External\Baz\Auth\Authenticator;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Builder;
use External\Baz\Auth\Responses\Failure;
class AuthenticationService
{
/**
* @var JWTBuilder
*/
/**
 * AuthService constructor.
 *
 * @param JWTBuilder $jwtBuilder
 */

/**
 * Authenticate a user.
 *
 * @param string $login
 * @param string $password
 *
 * @return JsonResponse
 *
 * @throws AuthenticationFailedException
 */
    public function authenticate(string $login, string $password): JsonResponse
    {
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

        if ($prefix === 'FOO') {
            AuthWS::authenticate($login, $password);
        } else if ($prefix === 'BAR') {
            $response = LoginService::login($login, $password);
            if (!$response) {
                throw new AuthenticationFailedException();
            }
        } else if ($prefix === 'BAZ') {
            $response = Authenticator::auth($login, $password);
            if ($response instanceof Failure) {
                throw new AuthenticationFailedException();
            }
        } else {
            throw new AuthenticationFailedException();
        }

        return json(['token' => $token]);
    }
}