<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\View\Helper;

use Authentication\IdentityInterface;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use Cake\View\Helper;
use RuntimeException;

/**
 * Identity Helper
 *
 * A convenience helper to access the identity data
 */
class IdentityHelper extends Helper
{
    /**
     * User data
     *
     * @array
     */
    protected $_userData = [];

    /**
     * Identity Object
     *
     * @var null|\Authentication\IdentityInterface
     */
    protected $_identity;

    /**
     * Constructor hook method.
     *
     * Implement this method to avoid having to overwrite the constructor and call parent.
     *
     * @param array $config The configuration settings provided to this helper.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->_identity = $this->getView()->request->getAttribute('identity');

        if (empty($this->_identity)) {
            return;
        }

        if (!$this->_identity instanceof IdentityInterface) {
            throw new RuntimeException(sprintf('Identity found in request does not implement %s', IdentityInterface::class));
        }

        $this->_userData = $this->_identity->getOriginalData();

        if ($this->_userData instanceof Entity) {
            $this->_userData = $this->_userData->toArray();
        }
    }

    /**
     * Gets the id of the current logged in identity
     *
     * @return int|null|string
     */
    public function getId()
    {
        if (empty($this->_identity)) {
            return null;
        }

        return $this->_identity->getIdentifier();
    }

    /**
     * Checks if a user is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return !empty($this->_identity);
    }

    /**
     * This check can be used to tell if a record that belongs to some user is the
     * current logged in user
     *
     * @param int|string $id Identity id to check against
     * @param string $field Name of the field in the identity data to check against, id by default
     * @return bool
     */
    public function is($id, $field = 'id')
    {
        return ($id === $this->get($field));
    }

    /**
     * Gets user data
     *
     * @param string $key Key of something you want to get from the identity data
     * @return mixed
     */
    public function get($key = null)
    {
        if ($key === null) {
            return $this->_userData;
        }

        return Hash::get($this->_userData, $key);
    }
}
