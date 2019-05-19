# Quick Start

## Installation

Install the library with [composer](https://getcomposer.org/).

```sh
composer require phauthentic/authentication
```

## Configuration

Example of configuring the authentication middleware using `authentication` application hook.

**This is an example**!

Please note that it is recommended to implement the service provider in its own 
class and pass it to the middleware as an instance. This makes it easy to use 
composition via a DI container to get your objects tied together.

```php
namespace MyApp;

use Application\Http\BaseApplication;
use Phauthentic\Authentication\AuthenticationService;
use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Phauthentic\Authentication\Authenticator\AuthenticatorCollection;
use Phauthentic\Authentication\Authenticator\FormAuthenticator;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Identifier\Resolver\PdoStatementResolver;
use Phauthentic\Authentication\Identity\DefaultIdentityFactory;
use Phauthentic\Authentication\Middleware\AuthenticationMiddleware;
use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Returns a service provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @param \Psr\Http\Message\ResponseInterface $response Response
     * @return \Phauthentic\Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): \Phauthentic\Authentication\AuthenticationServiceInterface
    {
        $authenticatorCollection = new AuthenticatorCollection();

        // The PdoStatementResolver needs a PDO Statement object
        // Put your real connection in here if you want to use this code
        // But it is suggested to inject this
        $pdo = new PDO(getenv('sqlite::memory:'));
        $statement = $statement = $pdo->query('SELECT * FROM users WHERE username = :username AND password = :password');

        $authenticatorCollection->add(new FormAuthenticator(
            new PasswordIdentifier(
                new PdoStatementResolver($statement),
                new DefaultPasswordHasher()
            ),
            new DefaultUrlChecker()
        ));

        return new AuthenticationService(
            $authenticatorCollection,
            new DefaultIdentityFactory()
        );
    }

    /**
     * @param mixed $middlewareQueue Whatever your middleware queue implementation is
     * @return mixed
     */
    public function middleware($middlewareQueue)
    {
        // Various other middlewares for error handling, routing etc. added here.

        // Add the authentication middleware
        $authentication = new AuthenticationMiddleware($this);

        // Add the middleware to the middleware queue
        $middlewareQueue->add($authentication);

        return $middlewareQueue;
    }
}

```

If one of the configured authenticators was able to validate the credentials,
the middleware will add the authentication service to the request object as an
attribute. If you're not yet familiar with request attributes [check the PSR7
documentation](http://www.php-fig.org/psr/psr-7/).

## Using Stateless Authenticators with other Authenticators

When using `HttpBasic` or `HttpDigest` with other authenticators, you should
remember that these authenticators will halt the request when authentication
credentials are missing or invalid. This is necessary as these authenticators
must send specific challenge headers in the response. If you want to combine
`HttpBasic` or `HttpDigest` with other authenticators, you may want to configure
these authenticators as the *last* authenticators:

```php
use Phauthentic\Authentication\AuthenticationService;
use Phauthentic\Authentication\Authenticator\SessionAuthenticator;
use Phauthentic\Authentication\Authenticator\HttpBasicAuthentcator;
use Phauthentic\Authentication\Authenticator\SessionAuthenticator;
use Phauthentic\Authentication\Authenticator\AuthenticatorCollection;

$authenticatorCollection = new AuthenticatorCollection();

$authenticatorCollection->add(new SessionAuthenticator(/*...*/);
$authenticatorCollection->add(new FormAuthenticator(/*...*/);
// Load the authenticators leaving Basic as the last one!
$authenticatorCollection->add(new HttpBasicAuthentcator(/*...*/);

$service = new AuthenticationService($authenticatorCollection);
```
