# Identity resolvers

Identity resolvers provide adapters for different datasources. They allow
you to control which source identities are searched in. They are separate from
the identifiers so that they can be swapped out independently from the
identifier method (form, jwt, basic auth).

Also the resolver is in charge of implementing any of the query logic.

So for example if you want to lookup an user based on 
`username OR email AND password` you'll have to set up these conditions in your 
resolver. The reason for that is, that it is simply not possible to cover all 
possibilities of every implementation.

## Callback Resolver

The callback resolver is good starting point for prototyping or might already be
 enough for your concrete implementation.

Just define a callable and pass it as constructor to the resolver.

Here is a *very* simple *example* that just checks if a given username is in a 
list of provided usernames and compares the passwords.

```php
use Phauthentic\Identifier\Resolver\CallbackResolver;

$userList = [
    'florian' => 'password',
    'robert' => 'password'
];

// You could wrap this in a class implementing __invoke() as well!
$callback = function($conditions) use ($userList) {
    if (isset($conditions['username']) 
        && isset($userList[$conditions['username']]) 
        && $userList[$conditions['username']] === $conditions['password'])
    ) {
        return ['username' => $conditions['username']];
    }

    return null;
);

$resolver = new CallbackResolver($callback);
```

Instead of using an array for the user data here, you could pass an instance of 
your favorite ORM object into the callable as well and implement your logic in 
it to resolve the user.

## Writing your own resolver

Any ORM or datasource can be adapted to work with authentication by creating a 
resolver. Resolvers must implement `\Phauthentic\Authentication\Identifier\Resolver\ResolverInterface`.

Resolver can be configured using setter methods, same as  the identifiers.
