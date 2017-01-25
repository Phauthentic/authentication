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

use RuntimeException;

/**
 * An exception that holds onto the headers/body for a challenge response.
 */
class ChallengeException extends RuntimeException
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $body = '';

    /**
     * Constructor
     *
     * @param array $headers The headers that should be sent in the challenge response.
     * @param string $body The response body that should be sent in the challenge response.
     * @param int $code The exception code that will be used as a HTTP status code
     */
    public function __construct(array $headers, $body = '', $code = 401)
    {
        parent::__construct('Authentication is required to continue', $code);
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
