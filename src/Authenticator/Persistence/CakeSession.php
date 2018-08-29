<?php
namespace Cake\Auth\Authenticator\Persistence;

use Authentication\Authenticator\Persistence\PersistenceInterface;
use Authentication\Authenticator\Persistence\SessionPersistenceInterface;
use SessionHandlerInterface;

/**
 * CakePHP Session persistence
 */
class CakeSession implements SessionPersistenceInterface
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
    public function __construct(SessionHandlerInterface $session, string $sessionKey)
    {
        $this->session = $session;
        $this->sessionKey = $sessionKey;
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
