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

/**
 * Pimple container wrapper.
 *
 * Acts as a container but proxies all container calls
 * to the member container.
 *
 * @package pimple
 * @author  Igor Wiedler
 */
class ContainerWrapper implements ContainerInterface
{
    private $container;

    public function hasContainer()
    {
        return isset($this->container);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($id, $value)
    {
        $this->throwExceptionUnlessHasContainer();

        $this->container[$id] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($id)
    {
        $this->throwExceptionUnlessHasContainer();

        return $this->container[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($id)
    {
        $this->throwExceptionUnlessHasContainer();

        return isset($this->container[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($id)
    {
        $this->throwExceptionUnlessHasContainer();

        unset($this->container[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function share(\Closure $callable)
    {
        $this->throwExceptionUnlessHasContainer();

        return $this->container->share($callable);
    }

    /**
     * {@inheritdoc}
     */
    public function protect(\Closure $callable)
    {
        $this->throwExceptionUnlessHasContainer();

        return $this->container->protect($callable);
    }

    /**
     * {@inheritdoc}
     */
    public function raw($id)
    {
        $this->throwExceptionUnlessHasContainer();

        return $this->container->protect($callable);
    }

    private function throwExceptionUnlessHasContainer()
    {
        if (!$this->hasContainer()) {
            throw new \RuntimeException("You must set a member container on the ContainerWrapper before using it.");
        }
    }
}
