# Authentication

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/Phauthentic/authentication/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/authentication/)
[![Code Quality](https://img.shields.io/scrutinizer/g/Phauthentic/authentication/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/authentication/)

This library intends to provide a framework around authentication and user identification. Authorization is a [separate concern](https://en.wikipedia.org/wiki/Separation_of_concerns).

## Installation

You can install this library using [composer](http://getcomposer.org):

```
composer require Phauthentic/authentication
```

## Requirements

Your application **must** use PSR HTTP Message interfaces for your request and response objects. The whole library is build to be framework agnostic but uses these interfaces as the common API. Every modern framework and application should fulfill this requirement.

## Framework integrations

 * [Laravel](https://github.com/Phauthentic/authentication-laravel)
 * [Doctrine](https://github.com/Phauthentic/authentication-doctrine)
 * [CakePHP](https://github.com/Phauthentic/authentication-cakephp)
 * [Yii](https://github.com/Phauthentic/authentication-yii)

## Documentation

 * [Quick Start and Introduction](docs/Quick-start-and-introduction.md)
 * [Authenticators](docs/Authenticators.md)
 * [Identifiers](docs/Identifiers.md)
 * [Identity Objects](docs/Identity-Object.md)
 * [URL Checkers](docs/URL-Checkers.md)

## Copyright & License

Licensed under the [MIT license](LICENSE.txt).

* Copyright (c) [Phauthentic](https://github.com/Phauthentic)
* Copyright (c) [Cake Software Foundation, Inc.](https://cakefoundation.org)
