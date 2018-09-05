<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

use Authentication\Identifier\IdentifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAuthenticator implements AuthenticatorInterface
{

    /**
     * Config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Identifier or identifiers collection.
     *
     * @var \Authentication\Identifier\IdentifierInterface
     */
    protected $_identifier;

    /**
     * Constructor
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier Identifier or identifiers collection.
     */
    public function __construct(IdentifierInterface $identifier)
    {
        $this->_identifier = $identifier;
    }

    /**
     * Gets the identifier.
     *
     * @return \Authentication\Identifier\IdentifierInterface
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Sets the identifier.
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier IdentifierInterface instance.
     * @return $this
     */
    public function setIdentifier(IdentifierInterface $identifier)
    {
        $this->_identifier = $identifier;

        return $this;
    }

    /**
     * Authenticate a user based on the request information.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to get authentication information from.
     * @param \Psr\Http\Message\ResponseInterface $response A response object that can have headers added.
     * @return \Authentication\Authenticator\ResultInterface Returns a result object.
     */
    abstract public function authenticate(ServerRequestInterface $request);
}
