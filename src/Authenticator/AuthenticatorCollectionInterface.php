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

declare(strict_types=1);

namespace Phauthentic\Authentication\Authenticator;

use IteratorAggregate;

/**
 * Authenticator Collection Interface
 */
interface AuthenticatorCollectionInterface extends IteratorAggregate
{
    /**
     * Adds a authenticator to the collection
     *
     * @param \Phauthentic\Authentication\Authenticator\AuthenticatorInterface $authenticator Authenticator instance.
     * @return void
     */
    public function add(AuthenticatorInterface $authenticator): void;

    /**
     * Checks if the collection is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
