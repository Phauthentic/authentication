# JWT Example

Requirements for this document:

 * You know how [JWT](https://jwt.io/) works
 * You've read [the quick start and introduction](Quick-start-and-introduction.md) guide
 
Assuming you've read the quick start guide, you'll remember the `getAuthenticationService()` method. Inside this method you already added the `FormAuthenticator`, now you're going to add the `JwtAuthenticator` to the `$authenticatorCollection`:

```php
$authenticatorCollection->(new JwtAuthenticator(
    new JwtSubjectIdentifier(
        $resolver
    ),
    'your-jwt-secret-goes-here'
));
```

This is enough for a very simple JWT authentication. For additional settings and ways to configure JWT take a look at the [authentictor](Authenticators.md) and [identifier](Identifiers.md) documentation.

This is a *very* simple request handler or controller action. The middleware has set the identity attribute to your request object if your form login was successful. You can now use the data it contains with your JWT token. Make sure you're not getting a ton of data, the user id should be enough.

```php
declare(strict_types = 1);

namespace App\Application\Http\Login\Action;

use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;

class LoginAction
{
	public function handle(ServerRequestInterface $request)
	{
		$identity = $request->getAttribute('identity');

		if ($identity) {
			return [
				'token' => JWT::encode($identity, 'secret key')
			];
		}

		return [
			'error' => 'Invalid credentials'
		];
	}
}
```

However your framework or your implementation is resolving the result of an action, you might need to return something else. Keep in mind, that this here is just a very simple example to give you an idea of how to use the library and how to get the token.
