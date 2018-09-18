<?php
declare(strict_types=1);
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

class Failure implements FailureInterface
{
    /**
     * @var \Authentication\Authenticator\AuthenticatorInterface
     */
    protected $authenticator;

    /**
     * @var \Authentication\Authenticator\ResultInterface
     */
    protected $result;

    /**
     * Constructor.
     *
     * @param \Authentication\Authenticator\AuthenticatorInterface $authenticator Authenticator.
     * @param \Authentication\Authenticator\ResultInterface $result Result.
     */
    public function __construct(AuthenticatorInterface $authenticator, ResultInterface $result)
    {
        $this->authenticator = $authenticator;
        $this->result = $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(): ResultInterface
    {
        return $this->result;
    }
}
