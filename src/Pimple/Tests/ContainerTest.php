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

use Pimple\Container;

/**
 * @author  Igor Wiedler <igor@wiedler.ch>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testWithString()
    {
        $container = new Container();
        $container['param'] = 'value';

        $this->assertEquals('value', $container['param']);
    }

    public function testWithClosure()
    {
        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };

        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $container['service']);
    }

    public function testServicesShouldBeDifferent()
    {
        $container = new Container();
        $container['service'] = $container->factory(function () {
            return new Fixtures\Service();
        });

        $serviceOne = $container['service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $container['service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testShouldPassContainerAsParameter()
    {
        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };
        $container['container'] = function ($container) {
            return $container;
        };

        $this->assertNotSame($container, $container['service']);
        $this->assertSame($container, $container['container']);
    }

    public function testIsset()
    {
        $container = new Container();
        $container['param'] = 'value';
        $container['service'] = function () {
            return new Fixtures\Service();
        };

        $container['null'] = null;

        $this->assertTrue(isset($container['param']));
        $this->assertTrue(isset($container['service']));
        $this->assertTrue(isset($container['null']));
        $this->assertFalse(isset($container['non_existent']));
    }

    public function testConstructorInjection()
    {
        $params = array('param' => 'value');
        $container = new Container($params);

        $this->assertSame($params['param'], $container['param']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $container = new Container();
        echo $container['foo'];
    }

    public function testOffsetGetHonorsNullValues()
    {
        $container = new Container();
        $container['foo'] = null;
        $this->assertNull($container['foo']);
    }

    public function testUnset()
    {
        $container = new Container();
        $container['param'] = 'value';
        $container['service'] = function () {
            return new Fixtures\Service();
        };

        unset($container['param'], $container['service']);
        $this->assertFalse(isset($container['param']));
        $this->assertFalse(isset($container['service']));
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testShare($service)
    {
        $container = new Container();
        $container['shared_service'] = $service;

        $serviceOne = $container['shared_service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $container['shared_service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testProtect($service)
    {
        $container = new Container();
        $container['protected'] = $container->protect($service);

        $this->assertSame($service, $container['protected']);
    }

    public function testGlobalFunctionNameAsParameterValue()
    {
        $container = new Container();
        $container['global_function'] = 'strlen';
        $this->assertSame('strlen', $container['global_function']);
    }

    public function testRaw()
    {
        $container = new Container();
        $container['service'] = $definition = $container->factory(function () { return 'foo'; });
        $this->assertSame($definition, $container->raw('service'));
    }

    public function testRawHonorsNullValues()
    {
        $container = new Container();
        $container['foo'] = null;
        $this->assertNull($container->raw('foo'));
    }

    public function testFluentRegister()
    {
        $container = new Container();
        $this->assertSame($container, $container->register($this->getMock('Pimple\ServiceProviderInterface')));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testRawValidatesKeyIsPresent()
    {
        $container = new Container();
        $container->raw('foo');
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testExtend($service)
    {
        $container = new Container();
        $container['shared_service'] = function () {
            return new Fixtures\Service();
        };
        $container['factory_service'] = $container->factory(function () {
            return new Fixtures\Service();
        });

        $container->extend('shared_service', $service);
        $serviceOne = $container['shared_service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceOne);
        $serviceTwo = $container['shared_service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceTwo);
        $this->assertSame($serviceOne, $serviceTwo);
        $this->assertSame($serviceOne->value, $serviceTwo->value);

        $container->extend('factory_service', $service);
        $serviceOne = $container['factory_service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceOne);
        $serviceTwo = $container['factory_service'];
        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceTwo);
        $this->assertNotSame($serviceOne, $serviceTwo);
        $this->assertNotSame($serviceOne->value, $serviceTwo->value);
    }

    public function testExtendDoesNotLeakWithFactories()
    {
        if (extension_loaded('pimple')) {
            $this->markTestSkipped('Pimple extension does not support this test');
        }
        $container = new Container();

        $container['foo'] = $container->factory(function () { return; });
        $container['foo'] = $container->extend('foo', function ($foo, $container) { return; });
        unset($container['foo']);

        $p = new \ReflectionProperty($container, 'values');
        $p->setAccessible(true);
        $this->assertEmpty($p->getValue($container));

        $p = new \ReflectionProperty($container, 'factories');
        $p->setAccessible(true);
        $this->assertCount(0, $p->getValue($container));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testExtendValidatesKeyIsPresent()
    {
        $container = new Container();
        $container->extend('foo', function () {});
    }

    public function testKeys()
    {
        $container = new Container();
        $container['foo'] = 123;
        $container['bar'] = 123;

        $this->assertEquals(array('foo', 'bar'), $container->keys());
    }

    /** @test */
    public function settingAnInvokableObjectShouldTreatItAsFactory()
    {
        $container = new Container();
        $container['invokable'] = new Fixtures\Invokable();

        $this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $container['invokable']);
    }

    /** @test */
    public function settingNonInvokableObjectShouldTreatItAsParameter()
    {
        $container = new Container();
        $container['non_invokable'] = new Fixtures\NonInvokable();

        $this->assertInstanceOf('Pimple\Tests\Fixtures\NonInvokable', $container['non_invokable']);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service definition is not a Closure or invokable object.
     */
    public function testFactoryFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container->factory($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callable is not a Closure or invokable object.
     */
    public function testProtectFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container->protect($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" does not contain an object definition.
     */
    public function testExtendFailsForKeysNotContainingServiceDefinitions($service)
    {
        $container = new Container();
        $container['foo'] = $service;
        $container->extend('foo', function () {});
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extension service definition is not a Closure or invokable object.
     */
    public function testExtendFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container['foo'] = function () {};
        $container->extend('foo', $service);
    }

    /**
     * Provider for invalid service definitions.
     */
    public function badServiceDefinitionProvider()
    {
        return array(
          array(123),
          array(new Fixtures\NonInvokable()),
        );
    }

    /**
     * Provider for service definitions.
     */
    public function serviceDefinitionProvider()
    {
        return array(
            array(function ($value) {
                $service = new Fixtures\Service();
                $service->value = $value;

                return $service;
            }),
            array(new Fixtures\Invokable()),
        );
    }

    public function testDefiningNewServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];

        $container['bar'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $container['bar']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot override frozen service "foo".
     */
    public function testOverridingServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];

        $container['foo'] = function () {
            return 'bar';
        };
    }

    public function testRemovingServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];

        unset($container['foo']);
        $container['foo'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $container['foo']);
    }

    public function testExtendingService()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $container['foo'] = $container->extend('foo', function ($foo, $app) {
            return "$foo.bar";
        });
        $container['foo'] = $container->extend('foo', function ($foo, $app) {
            return "$foo.baz";
        });
        $this->assertSame('foo.bar.baz', $container['foo']);
    }

    public function testExtendingServiceAfterOtherServiceFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $container['bar'] = function () {
            return 'bar';
        };
        $foo = $container['foo'];

        $container['bar'] = $container->extend('bar', function ($bar, $app) {
            return "$bar.baz";
        });
        $this->assertSame('bar.baz', $container['bar']);
    }
}
