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

namespace Phauthentic\Authentication\Identifier;

use ArrayAccess;
use Phauthentic\Authentication\Identifier\Resolver\ResolverInterface;

/**
 * Token Identifier
 */
class TokenIdentifier extends AbstractIdentifier
{
    /**
     * Resolver
     *
     * @var \Phauthentic\Authentication\Identifier\Resolver\ResolverInterface
     */
    protected ResolverInterface $resolver;

    /**
     * Token Field
     *
     * @var string
     */
    protected string $tokenField = 'token';

    /**
     * Data field
     *
     * @var string|null
     */
    protected ?string $dataField = self::CREDENTIAL_TOKEN;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver Resolver instance.
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Sets data field
     *
     * @param null|string $field Field name
     * @return $this
     */
    public function setDataField(?string $field): self
    {
        $this->dataField = $field;

        return $this;
    }

    /**
     * Sets the token field
     *
     * @param string $field Field name
     * @return $this
     */
    public function setTokenField(string $field): self
    {
        $this->tokenField = $field;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $data): ?ArrayAccess
    {
        if (!isset($data[$this->dataField])) {
            return null;
        }

        $conditions = [
            $this->tokenField => $data[$this->dataField]
        ];

        return $this->resolver->find($conditions);
    }
}
