# Identity resolvers

Identity resolvers provide adapters for different datasources. They allow
you to control which source identities are searched in. They are separate from
the identifiers so that they can be swapped out independently from the
identifier method (form, jwt, basic auth).

## Callback Resolver

The callback resolver is good starting point for prototyping or might already be enough for your concrete implementation.

Just define a callable and pass it as constructor to the resolver.

Here is a *very* simple *example* that just checks if a given username is in a list of provided usernames:

```php
$userList = ['florian', 'robert'];

$callback = function() use ($userList) {
    return isset($conditions['username']) && in_array($conditions['username'], $userList);
);

$resolver = new callbackResolver($callback);
```

## CakePHP ORM Resolver

Identity resolver for the CakePHP ORM.

Configuration option setters:

* **setUserModel()**: The user model identities are located in. Default is `Users`.
* **setFinder()**: The finder to use with the model. Default is `all`.

In order to use ORM resolver you must require `cakephp/orm` in your `composer.json` file.

## Writing your own resolver

Any ORM or datasource can be adapted to work with authentication by creating a resolver.  Resolvers must implement `\Phauthentic\Authentication\Identifier\Resolver\ResolverInterface`.

Resolver can be configured using setter methods, same as  the identifiers.
