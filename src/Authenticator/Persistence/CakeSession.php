<?php
namespace Cake\Auth\Authenticator\Persistence;

use Cake\Http\Session;

/**
 * CakePHP Session persistence
 */
class CakeSession
{

    /**
     * Session
     *
     * @var \Cake\Http\Session
     */
    protected $session;

    /**
     * Session Key
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * Constructor
     */
    public function __construct(Session $session, string $sessionKey)
    {
        $this->session = $session;
    }

    public function persistIdentity($identity)
    {
        $this->session->write($this->sessionKey, $identity);
    }

    public function clearIdentity()
    {
        $this->session->delete($this->sessionKey);
    }
}
