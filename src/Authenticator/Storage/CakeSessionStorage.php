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
namespace Authentication\Authenticator\Storage;

use Cake\Http\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Storage adapter for the CakePHP Session
 */
class CakeSessionStorage implements StorageInterface
{
    /**
     * Default Config
     *
     * @var array
     */
    protected $defaultConfig = [
        'sessionKey' => 'Auth',
        'sessionAttribute' => 'session',
    ];

    /**
     * Config
     *
     * @var array
     */
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config + $this->defaultConfig;
    }

    public function clear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->getSession($request)->delete($this->config['sessionKey']);

        return $response;
    }

    public function read(ServerRequestInterface $request)
    {
        return $this->getSession($request)->read($this->config['sessionKey']);
    }

    public function write(ServerRequestInterface $request, ResponseInterface $response, $data): ResponseInterface
    {
        $this->getSession($request)->write($this->config['sessionKey'], $data);

        return $response;
    }

    protected function getSession(ServerRequestInterface $request): Session
    {
        return $request->getAttribute($this->config['sessionAttribute']);
    }
}
