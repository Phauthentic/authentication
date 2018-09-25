# Identity resolvers

Identity resolvers are adapters for different datasources. They allow
you to control which source identities are searched in. They are separate from
the identifiers so that they can be swapped out independently from the
identifier method (form, jwt, basic auth).

Also the resolver is in charge of implementing the query logic.

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

## PDO Statement Resolver

The PDO Statement resolver will, as the name implies, take an instance of 
`\PDOStatement`. Prepare your query and you'll have to use the `:placeholder` 
notation, so that the resolver can insert the correct values.

The names might be different depending on your configuration of the identifiers
that pass the data as an array of `['name' => 'value']` to your resolver.

Remember, it's up to your resolver to implement the query. So write it according
your needs. Sometimes you might want to use the username to compare it against
an `username` and `email` field. So write your statement as you need it.  

```php
use PDO;
use Phauthentic\Identifier\Resolver\PdoStatementResolver;

// Get your PDO instance from your library / framework or create it
$pdo = new PDO(getenv('sqlite::memory:'));
$statement = $statement = $pdo->query('SELECT * FROM users WHERE username = :username AND password = :password');

$resolver = new PdoStatementResolver($statement);
```

## Writing your own resolver

Any ORM or datasource can be adapted to work with authentication by creating a 
resolver. Resolvers must implement `\Phauthentic\Authentication\Identifier\Resolver\ResolverInterface`.

Resolver can be configured using setter methods, same as  the identifiers.
