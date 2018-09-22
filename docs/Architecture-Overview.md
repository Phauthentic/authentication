# Architecture Overview

This document will provide you a high level overview of the design this library uses.

The basic idea is that all data that is required for authenticating an user is usually always in the request data available. To make this library agnostic to any framework but to ensure it is interoperable, we've decided to use the PSR HTTP Message Interfaces as the common base:

 * `Psr\Http\Message\ServerRequestInterface`
 * `Psr\Http\Message\ResponseInterface`

**Identifiers** will extract the credentials or any other information required to figure out an identity from a request object implementing `\Psr\Http\Message\ServerRequestInterface`. 

Some but not all identifiers will pass the credentials to **Resolvers**, that use the information the identifier collected, to lookup the identity in a database, LDAP or any other system.

**Authenticators** take an identifier to resove the identity, handle errors and return a result object.

The **Authentication Service** of this library provides an interface that is thought to be used in a middleware (we provide a PSR15 middleware) or inside your application layer.

Redirecting based on the result is intentionally **not** handled by the library because this depends very much on the implementation details of each application and should be done in the application layer by the application itself, using either the service or the result object that is set as request attribute in the case the middleware was used.
