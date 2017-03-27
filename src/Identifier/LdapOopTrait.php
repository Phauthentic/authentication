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
namespace Authentication\Identifier;

/**
 * Provides a very thin OOP wrapper around the ldap_* functions.
 *
 * We don't need and want a huge LDAP lib for our purpose.
 *
 * But this makes it easier to unit test code that is using ldap because we can
 * mock it very easy. It also provides some convenience.
 */
trait LdapOopTrait
{

    /**
     * LDAP Object
     *
     * @var object
     */
    protected $ldapConnection;

    /**
     * Bind to LDAP directory
     *
     * @param resource|null $bind An LDAP link identifier
     * @param string|null $password Bind password
     * @return bool
     */
    public function ldapBind($bind = null, $password = null)
    {
        return ldap_bind($this->getLdapConnection(), $bind, $password);
    }

    /**
     * Return first result id
     *
     * @param resource $searchResults Result identifier
     * @return resource|false Returns the result entry identifier
     */
    public function ldapFirstEntry($searchResults)
    {
        return ldap_first_entry($this->getLdapConnection(), $searchResults);
    }

    /**
     * Search LDAP tree
     *
     * @param string $filter The search filter
     * @param string $attribute The required attributes
     * @return resource|false
     */
    public function ldapSearch($filter, $attribute)
    {
        return ldap_search($this->getLdapConnection(), $this->_config['baseDN'], $filter, $attribute);
    }

    /**
     * Get the LDAP connection
     *
     * @return mixed
     * @throws \RuntimeException If the connection is empty
     */
    public function getLdapConnection()
    {
        if (empty($this->ldapConnection)) {
            throw new \RuntimeException('You are not connected to a LDAP server.');
        }

        return $this->ldapConnection;
    }

    /**
     * Connect to an LDAP server
     *
     * @param string $host Hostname
     * @param int $port Port
     * @return void
     */
    public function ldapConnect($host, $port = null)
    {
        $this->ldapConnection = ldap_connect($host, $port);
    }

    /**
     * Get attributes from a search result entry
     *
     * @param resource $entry Result entry identifier
     * @return array|false
     */
    public function ldapGetAttributes($entry)
    {
        return ldap_get_attributes($this->getLdapConnection(), $entry);
    }

    /**
     *  Set the value of the given option
     *
     * @param int $option Option to set
     * @param mixed $value The new value for the specified option
     * @return void
     */
    public function ldapSetOption($option, $value)
    {
        ldap_set_option($this->getLdapConnection(), $option, $value);
    }

    /**
     * Get the current value for given option
     *
     * @param int $option Option to get
     * @return mixed This will be set to the option value.
     */
    public function ldapGetOption($option)
    {
        ldap_get_option($this->getLdapConnection(), $option, $retval);

        return $retval;
    }

    /**
     * Unbind from LDAP directory
     *
     * @return void
     */
    public function ldapUnbind()
    {
        ldap_unbind($this->ldapConnection);
        $this->ldapConnection = null;
    }
}
