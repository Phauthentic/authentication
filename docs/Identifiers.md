# Identifiers

Identifiers will identify an user or service based on the information that was extracted from the request by the authenticators. A holistic example of using the Password Identifier looks like:

```php
use Phauthentic\Identifier\PasswordIdenfier;
use Phauthentic\Identifier\Resolver\OrmResolver;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;

$identifier = new PasswordIdenfier(
    new OrmResolver(),
    new DefaultPasswordHasher()
);
```

Some identifiers might use other constructor arguments. Construct them manually or set them up in your DI config as needed.

## Identifier options

Almost each identifier takes a few different configuration options. The options can be set through setter methods. The following list of identifiers describes their setter options: 


## Password Identifier

The password identifier checks the passed credentials against a datasource.

Configuration option setters:

* **setFields()**: The fields for the lookup. Default is `['username' => 'username', 'password' => 'password']`.
  You can also set the `username` to an array. For e.g. using
  `['username' => ['username', 'email'], 'password' => 'password']` will allow
  you to match value of either username or email columns.

## Token Identifier

Checks the passed token against a datasource.

Configuration option setters:

* **setTokenField()**: The field in the database to check against. Default is `token`.
* **setDataField()**: The field in the passed data from the authenticator. Default is `token`.

## JWT Subject Identifier

Checks the passed JWT token against a datasource.

Configuration option setters:

* **setTokenField()**: The field in the database to check against. Default is `id`.
* **setDataField()**: The payload key to get user identifier from. Default is `sub`.
* **setResolver()**: The identity resolver. Default is `Authentication.Orm` which uses CakePHP ORM.

## LDAP Identifier

Checks the passed credentials against a LDAP server. This identifier requires the PHP LDAP extension.

Configuration option setters:

* **setFields()**: The fields for the lookup. Default is `['username' => 'username', 'password' => 'password']`.
* **setHost()**: The FQDN of your LDAP server.
* **setPort()**: The port of your LDAP server. Defaults to `389`.
* **setBindDN()**: The Distinguished Name of the user to authenticate. Must be a callable. Anonymous binds are not supported.
* **setLdap()**: The extension adapter. Defaults to `\Authentication\Identifier\Ldap\ExtensionAdapter`.
  You can pass a custom object/classname here if it implements the `AdapterInterface`.
* **setOptions()**: Additional LDAP options, like `LDAP_OPT_PROTOCOL_VERSION` or `LDAP_OPT_NETWORK_TIMEOUT`.
  See [php.net](http://php.net/manual/en/function.ldap-set-option.php) for more valid options.

## Callback Identifier

Allows you to use a callback for identification. This is useful for simple identifiers or quick prototyping.

Configuration option setters:

* **setCallback()**: Default is `null` and will cause an exception. You're required to pass a valid callback to this option to use the authenticator.
