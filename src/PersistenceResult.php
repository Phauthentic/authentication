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
namespace Authentication;

use ArrayAccess;
use http\Env\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Persistence Result Interface
 */
class PersistenceResult implements PersistenceResultInterface
{
    /**
     * Response
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Request
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Psr\Http\Message\RequestInterface
     * @param \Psr\Http\Message\ResponseInterface
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->response = $response;
        $this->request = $response;
    }

    /**
     * Get the primary key/id field for the identity.
     *
     * @return string|int|null
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Gets the original data object.
     *
     * @return \ArrayAccess|array
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
