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

namespace Pimple\Tests;

use Pimple;

class ConvenienceMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterSharedService()
    {
        $pimple = new Pimple();
        $pimple->registerSharedService('foo', 'Pimple\Tests\Service');

        $this->assertInstanceOf('Pimple\Tests\Service', $pimple['foo']);
        $this->assertSame($pimple['foo'], $pimple['foo']);
    }

    public function testRegisterSharedServiceWithDeps()
    {
        $pimple = new Pimple();
        $pimple->registerSharedService('bar', 'Pimple\Tests\Service');
        $pimple->registerSharedService('foo', 'Pimple\Tests\Foo', array('bar'));

        $this->assertInstanceOf('Pimple\Tests\Foo', $pimple['foo']);
        $this->assertInstanceOf('Pimple\Tests\Service', $pimple['bar']);
        $this->assertSame($pimple['foo']->getBar(), $pimple['bar']);
    }

    public function testExtendService()
    {
        $pimple = new Pimple();
        $pimple['foo'] = $pimple->share(function () {
            return new Service();
        });
        $oldFoo = $pimple['foo'];

        $pimple->extendService('foo', function ($foo, $pimple) {
            return new Foo($foo);
        });

        $this->assertInstanceOf('Pimple\Tests\Foo', $pimple['foo']);
        $this->assertInstanceOf('Pimple\Tests\Service', $pimple['foo']->getBar());
        $this->assertSame($pimple['foo'], $pimple['foo']);
        $this->assertSame($oldFoo, $pimple['foo']->getBar());
    }
}
