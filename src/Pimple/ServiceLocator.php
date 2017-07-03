<?php

/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2009 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Pimple;

use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;

/**
 * Iterable Pimple/PSR-11 service locator.
 *
 * @author Pascal Luna <skalpa@zetareticuli.org>
 */
class ServiceLocator implements \Countable, \Iterator, ContainerInterface
{
    private $container;
    private $aliases = array();

    /**
     * ServiceLocator constructor.
     *
     * @param Container $container The Container instance used to locate services
     * @param array     $ids       Array of service ids that can be located. String keys can be used to define aliases
     */
    public function __construct(Container $container, array $ids)
    {
        $this->container = $container;

        foreach ($ids as $key => $id) {
            $this->aliases[is_int($key) ? $id : $key] = $id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->aliases[$id])) {
            throw new UnknownIdentifierException($id);
        }

        return $this->container[$this->aliases[$id]];
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->aliases[$id]);
    }

    public function count()
    {
        return count($this->aliases);
    }

    public function rewind()
    {
        reset($this->aliases);
    }

    public function current()
    {
        return $this->container[current($this->aliases)];
    }

    public function key()
    {
        return key($this->aliases);
    }

    public function next()
    {
        next($this->aliases);
    }

    public function valid()
    {
        return null !== key($this->aliases);
    }
}
