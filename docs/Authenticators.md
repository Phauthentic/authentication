# Authenticators

Authenticators handle convert request data into an authentication operations.
They leverage [Identifiers](./Identifiers.md) to find a known
[Identity](./Identity-Object.md).

## Session

This authenticator will check the session if it contains user data or
credentials. When using any stateful authenticators like `Form` listed below, be
sure to load `Session` authenticator first so that once logged in user data is
fetched from session itself on subsequent requests.

Configuration setters:

* **setCredentialFields()**: Set the fields to use to verify a user by.
* **enableVerification()**: Enable identity verification after it is retrieved from the session storage.
* **disableVerification()**: Disable identity verification.

## Form

Looks up the data in the request body, usually when a form submit happens via 
POST / PUT.

Configuration setters:

* **setLoginUrl()**: The login URL, String . Default is `null` and all pages will be checked.
* **setLoginUrls()**: Array of URLs.
* **setCredentialFields()**: Array that maps `username` and `password` to the specified POST data fields.

## Token

The token authenticator can authenticate a request based on a token that comes 
along with the request in the headers or in the request parameters.

Configuration setters:

* **setQueryParam()**: Name of the query parameter. Configure it if you want to get 
* **setHeaderName()**: Name of the header. Configure it if you want to get the token from the header.
* **setTokenPrefix()**: The optional token prefix.

## JWT

The JWT authenticator gets the [JWT token](https://jwt.io/) from the header or 
query param and either returns the payload directly or passes it to the 
identifiers to verify them against another datasource for example.

* **setHeader()**: The header line to check for the token. The default is `Authorization`.
* **setQueryParam()**: The query param to check for the token. The default is `token`.
* **setTokenPrefix()**: The token prefix. Default is `bearer`.
* **setAlgorithms()**: An array of hashing algorithms for Firebase JWT. Default is an array `['HS256']`.
* **setReturnPayload()**: To return or not return the token payload directly without going through the identifiers. Default is `true`.
* **setSecretKey()**: Default is `null` but you're **required** to pass a secret key if you're not in the context of a CakePHP application that provides it through `Security::salt()`.

If you want to identify the user based on the `sub` (subject) of the token you 
can use the `JwtSubject` identifier.

```php
$authenticator = new JwtAuthenticator(
    new JwtSubject()
);
$authenticator->setReturnPayload(false);
```

## HttpBasic

See https://en.wikipedia.org/wiki/Basic_access_authentication

Configuration setters:

* **setRealm()**: Default is `$_SERVER['SERVER_NAME']` override it as needed.

## HttpDigest

See https://en.wikipedia.org/wiki/Digest_access_authentication

Configuration setters:

* **setRealm()**: Sets the realm.
* **setQop()**: Sets Qop.
* **setNonce()**: Sets the nounce.
* **setOpaque()**: Sets Opaque.
* **setNonceLifetime()**: Sets the nonce lifetime.
* **setPasswordField()**: Sets the password field name.

## Cookie Authenticator aka "Remember Me"

The Cookie Authenticator allows you to implement the "remember me" feature for your login forms.

Just make sure your login form has a field that matches the field name that is configured in this authenticator.

To encrypt and decrypt your cookie make sure you added the EncryptedCookieMiddleware to your app *before* the AuthenticationMiddleware. 

Configuration setters:

* **setRememberMeField()**: Default is `remember_me`
* **setCookie()**: Array of cookie options:
  * **setname()**: Cookie name, default is `CookieAuth`
  * **setexpire()**: Expiration, default is `null`
  * **setpath()**: Path, default is `/`
  * **setdomain()**: Domain, default is an empty string ``
  * **setsecure()**: Bool, default is `false`
  * **sethttpOnly()**: Bool, default is `false`
  * **setvalue()**: Value, default is an empty string ``
* **setFields()**: Array that maps `username` and `password` to the specified identity fields.
* **setUrlChecker()**: The URL checker class or object. Default is `DefaultUrlChecker`.
* **setLoginUrl()**: The login URL, string or array of URLs. Default is `null` and all pages will be checked.
* **setPasswordHasher()**: Password hasher to use for token hashing. Default is `DefaultPasswordHasher::class`.

## OAuth

There are currently no plans to implement an OAuth authenticator.
The main reason for this is that OAuth 2.0 is not an authentication protocol.

Read more about this topic [here](https://oauth.net/articles/authentication/).

We will maybe add an OpenID Connect authenticator in the future.

## Url Checkers

Some authenticators like `Form` or `Cookie` should be executed only on certain pages like `/login` page. This can be achieved using Url Checkers.

By default a `DefaultUrlChecker` is used, which uses string URLs for comparison with support for regex check.

Configuration setters:

* **setUseRegex()**: Whether or not to use regular expressions for URL matching. Default is `false`.
* **setCheckFullUrl()**: Whether or not to check full URL. Useful when a login form is on a different subdomain. Default is `false`.

A custom url checker can be implemented for example if a support for framework specific URLs is needed. 
In this case the `Authentication\UrlChecker\UrlCheckerInterface should be implemented.

For more details about URL Checkers [see this documentation page](URL-Checkers.md). 
