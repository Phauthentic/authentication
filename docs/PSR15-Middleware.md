# PSR15 Middleware

The library comes with a PSR 15 middleware implementation. The middleware expects a service provider object. The object must implement the `Phauthentic\Authentication\AuthenticationServiceProviderInterface`. 

```php
namespace Application\ServiceProvider;

use \Phauthentic\Authentication\AuthenticationService;
use \Phauthentic\Authentication\Middleware\AuthenticationMiddleware;

class AuthenticationServiceProvider implements AuthenticationServiceProviderInterface
{
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        // Configure the service here or use your favorite DI container
        // there are plenty of possibilities!
        $authenticatorCollection = new AuthenticatorCollection();
        
        $authenticatorCollection->add(new FormAuthenticator(
            new PasswordIdenfier(
                new OrmResolver(),
                new DefaultPasswordHasher()
            ),
            new DefaultUrlChecekr()
        ));
        
        return new AuthenticationService($authenticatorCollection);
    }
}
```

Then use it in your middleware:

```php
use \Application\ServiceProvider\AuthenticationServiceProvider;
use \Phauthentic\Authentication\Middleware\AuthenticationMiddleware;

$middleware = new AuthenticationMiddleware($new AuthenticationServiceProvider);

// Now just add the middleware to your middleware queue
```
