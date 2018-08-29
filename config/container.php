<?php
use Authentication\AuthenticationService;
use Authentication\Authenticator\AuthenticatorCollection;
use Authentication\Authenticator\FormAuthenticator;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identifier\Resolver\OrmResolver;
use Authentication\PasswordHasher\DefaultPasswordHasher;

$container = function(): array {
    // Password Hashers
    $defaultHasher = new DefaultPasswordHasher();

    // URL Checker
    $defaultUrlChecker = new Authentication\UrlChecker\DefaultUrlChecker();

    // Identifiers & Resolver
    $ormResolver = new OrmResolver();
    $passwordIdentifier = new PasswordIdentifier($ormResolver, $defaultHasher);
    $identifierCollection = new IdentifierCollection();
    $identifierCollection->add($passwordIdentifier);

    // Authenticators
    $formAuthenticator = new FormAuthenticator($identifierCollection, $defaultUrlChecker);
    $authenticatorCollection = new AuthenticatorCollection($identifierCollection);

    // Service
    $service = new AuthenticationService($authenticatorCollection);

    return [
        'authentication.service' => $service,
        'authenticator.collection' => $authenticatorCollection,
        'identifier.collection' => $identifierCollection,
    ];
};
