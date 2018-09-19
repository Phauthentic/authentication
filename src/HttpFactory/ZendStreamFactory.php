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
namespace Phauthentic\Authentication\HttpFactory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Stream;

/**
 * Zend Stream Factory
 */
class ZendStreamFactory implements StreamFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://memory', 'rw');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * @inheritdoc
     */
    public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($file, $mode);

        return $this->createStreamFromResource($resource);
    }

    /**
     * @inheritdoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
