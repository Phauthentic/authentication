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
namespace Authentication\Identifier;

use Authentication\Identifier\Resolver\ResolverInterface;

/**
 * Token Identifier
 */
class TokenIdentifier extends AbstractIdentifier
{

    /**
     * Resolver
     *
     * @var \Authentication\Identifier\Resolver\ResolverInterface
     */
    protected $resolver;

    protected $tokenField = 'token';

    protected $dataField = self::CREDENTIAL_TOKEN;

    public function setDataField(?string $field): self
    {
        $this->dataField = $field;

        return $this;
    }

    public function setTokenField(?string $field): self
    {
        $this->tokenField = $field;

        return $this;
    }

    /**
     * Constructor
     *
     * @param array $config Configuration
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $data)
    {
        $dataField = $this->dataField;
        if (!isset($data[$dataField])) {
            return null;
        }

        $conditions = [
            $this->tokenField => $data[$dataField]
        ];

        return $this->resolver->find($conditions);
    }
}
