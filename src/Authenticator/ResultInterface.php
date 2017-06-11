<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

interface ResultInterface
{
    /**
     * General Failure
     */
    const FAILURE = 0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND = -1;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID = -2;

    /**
     * Failure due to other circumstances.
     */
    const FAILURE_OTHER = -3;

    /**
     * The authentication credentials were not found in the request.
     */
    const FAILURE_CREDENTIALS_NOT_FOUND = -4;

    /**
     * Authentication success.
     */
    const SUCCESS = 1;

    /**
     * Returns whether the result represents a successful authentication attempt.
     *
     * @return bool
     */
    public function isValid();

    /**
     * Get the result code for this authentication attempt.
     *
     * @return int
     */
    public function getCode();

    /**
     * Returns the identity used in the authentication attempt.
     *
     * @return \ArrayAccess|null
     */
    public function getIdentity();

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful.
     *
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getErrors();
}
