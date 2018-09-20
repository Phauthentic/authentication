# Architecture Overview

This document will provide you a highlevel overview of the design this library uses.

**Identifiers** will extract the credentials or any other information required to figure out an identity from a request object implementing `\Psr\Http\Message\ServerRequestInterface`. 

Some but not all identifiers will pass the credentials to **Resolvers**, that use the information the identifier collected, to lookup the identity in a database, LDAP or any other system.

**Authenticators** take an identifier to resove the identity, handle errors and return a result object.
