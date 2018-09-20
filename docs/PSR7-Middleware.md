# PSR7 Middleware

Because PSR7 doesn't define an interface for the middleware, we assume the 
presence of the PSR ServerRequestInterface **and** ResponseInterface in your 
iddleware queue.

The *example* implementation here is assuming that your queue handler is a 
callable.

It should be very easy to implement the authentication library in a PSR7
middleware by using the `AuthenticationService`.

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR 7 Authenticator Middleware
 */
class Psr7AuthenticationMiddleware extends AuthenticationMiddleware
{

    public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $service = $this->provider->getAuthenticationService($request);
        $request = $this->addAttribute($request, $this->serviceAttribute, $service);

        $service->authenticate($request);

        $identity = $service->getIdentity();
        $request = $this->addAttribute($request, $this->identityAttribute, $identity);

        $response = $next($request, $response);
        if ($response instanceof ResponseInterface) {
            $result = $service->persistIdentity($request, $response);

            return $result->getResponse();
        }

        return $response;
    }
}
```
