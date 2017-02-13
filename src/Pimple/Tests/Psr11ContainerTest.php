<?php

/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2017 Fabien Potencier
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

namespace Pimple\Tests;

use Pimple\Psr11Container;
use Psr\Container\ContainerInterface;

class Psr11ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testItIsStandardCompliant()
    {
        $container = new Psr11Container();
        $this->assertInstanceOf('\Psr\Container\ContainerInterface', $container);
    }

    public function testItCanGetServices()
    {
        $container = new Psr11Container();

        $serviceSet = new \stdClass();

        $container['service-id'] = function (ContainerInterface $container) use ($serviceSet) {
            return $serviceSet;
        };

        $serviceRetrieved = $container->get('service-id');

        $this->assertSame($serviceSet, $serviceRetrieved);
    }

    public function testItCanThrowANotFoundException()
    {
        $this->setExpectedException('\Psr\Container\NotFoundExceptionInterface');
        $container = new Psr11Container();
        $container->get('service-id');
    }

    public function testItCanTellIfItHasServices()
    {
        $container = new Psr11Container();
        $this->assertFalse($container->has('service-id'));
        $container['service-id'] = new \stdClass();
        $this->assertTrue($container->has('service-id'));
    }
}
