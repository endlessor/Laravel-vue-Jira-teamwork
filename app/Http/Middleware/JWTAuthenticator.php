<?php

namespace App\Http\Middleware;

use App\JWT\Authentication\JWT;
use App\JIRA\Tenant;
use App\User;
use Closure;

/**
 * Class JWTAuthenticator
 * @package App\Http\Middleware
 */
class JWTAuthenticator
{
    const TOKEN_LIFETIME = 1800;

    public function handle($request, Closure $next)
    {
        $jwt = $request->query->get('jwt');
        if(!$jwt && $request->headers->has("authorization")) {
            $authorizationHeaderArray = explode(" ",$request->headers->get("authorization"));
            if(count($authorizationHeaderArray) > 1) {
                $jwt = $authorizationHeaderArray[1];
            }
        }

        if (!$jwt) {
            abort(403, 'No jwt authentication found.');
        }

        $decodedToken = JWT::decode($jwt);
        if (!$decodedToken) {
            abort(403, 'Could not decode jwt information.');
        }

        $tenant = Tenant::fromClientKey($decodedToken->iss);
        if (!$tenant) {
            abort(403, 'Invalid tenant found.');
        }

        JWT::decode($jwt, $tenant->sharedSecret, ['HS256'], self::TOKEN_LIFETIME);

        Tenant::setAuthenticatedTenant($tenant);

        // Also load user.
        if ($decodedToken->context) {
            $context = $decodedToken->context;
            if (isset($context->user)) {
                $user = $this->getUser($tenant, $context->user);
                \Auth::setUser($user);
            }
        }

        $request->attributes->set('jwt-token', $jwt);

        return $next($request);
    }

    /**
     * @param Tenant $tenant
     * @param \stdClass $obj
     * @return User
     */
    private function getUser(Tenant $tenant, \stdClass $obj)
    {
        $user = User::fromKey($tenant, $obj->userKey);
        if (!$user) {
            $user = new User();
            $user->tenant()->associate($tenant);
        }

        $user->key = $obj->userKey;
        $user->username = $obj->username;
        $user->name = $obj->displayName;

        $user->save();

        return $user;
    }
}