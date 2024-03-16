# Authentication

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/Phauthentic/authentication/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/authentication/?branch=2.0)
[![Code Quality](https://img.shields.io/scrutinizer/g/Phauthentic/authentication/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/authentication/?branch=2.0)
![phpstan Level 8](https://img.shields.io/badge/phpstan-Level%208-brightgreen?style=flat-square)
![php 8.0](https://img.shields.io/badge/php-8.0-blue?style=flat-square)


This library intends to provide a framework around authentication and user identification. Authorization is a [separate concern](https://en.wikipedia.org/wiki/Separation_of_concerns).

## Installation

You can install this library using [composer](http://getcomposer.org):

```
composer require phauthentic/authentication
```

## Requirements

Your application **must** use the [PSR 7 HTTP Message interfaces](https://github.com/php-fig/http-message) for your request and response objects. The whole library is build to be framework agnostic but uses these interfaces as the common API. Every modern and well written framework and application should fulfill this requirement.

 * php >= 8.0
 * [psr/http-message](https://github.com/php-fig/http-message)

Only if you plan to use the PSR-15 middleware:

 * [psr/http-server-handler](https://github.com/php-fig/http-server-handler)
 * [psr/http-factory](https://github.com/php-fig/http-factory)
 * [psr/http-server-middleware](https://github.com/php-fig/http-server-middleware)

## Documentation

 * [Architectural Overview](docs/Architecture-Overview.md)
 * [Quick Start and Introduction](docs/Quick-start-and-introduction.md)
   * [JWT Example](docs/JWT-Example.md)
 * [Authenticators](docs/Authenticators.md)
   * [Session](docs/Authenticators.md#session)
   * [Token](docs/Authenticators.md#token)
   * [JWT](docs/Authenticators.md#jwt)
   * [HTTP Basic](docs/Authenticators.md#httpbasic)
   * [HTTP Digest](docs/Authenticators.md#httpdigest)
   * [Cookie](docs/Authenticators.md#cookie-authenticator-aka-remember-me)
   * [OAuth](docs/Authenticators.md#oauth)
 * [Identifiers](docs/Identifiers.md)
   * [Identity Resolvers](docs/Identity-Resolvers.md)
     * [Callback Resolver](docs/Identity-Resolvers.md#callback-resolver)
     * [PDO Statement Resolver](docs/Identity-Resolvers.md#pdo-statement-resolver)
     * [Writing your own Resolver](docs/Identity-Resolvers.md#writing-your-own-resolver)
 * [Identity Objects](docs/Identity-Object.md)
 * [URL Checkers](docs/URL-Checkers.md)
 * [PSR15 Middleware](docs/PSR15-Middleware.md)
 * [PSR7 Middleware](docs/PSR7-Middleware.md)

## Copyright & License

Licensed under the [MIT license](LICENSE.txt).

* Copyright (c) [Phauthentic](https://github.com/Phauthentic)
* Copyright (c) [Cake Software Foundation, Inc.](https://cakefoundation.org)
