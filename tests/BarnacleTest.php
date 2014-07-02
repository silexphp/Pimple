<?php

namespace Barnacle\Tests;

use Barnacle\Container;

/**
 * @author  Igor Wiedler <igor@wiedler.ch>
 */
class BarnacleTest extends \PHPUnit_Framework_TestCase
{
    public function testWithString()
    {
        $barnacle = new Container();
        $barnacle['param'] = 'value';

        $this->assertEquals('value', $barnacle['param']);
    }

    public function testWithClosure()
    {
        $barnacle = new Container();
        $barnacle['service'] = function () {
            return new Fixtures\Service();
        };

        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $barnacle['service']);
    }

    public function testServicesShouldBeDifferent()
    {
        $barnacle = new Container();
        $barnacle['service'] = $barnacle->factory(function () {
            return new Fixtures\Service();
        });

        $serviceOne = $barnacle['service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $barnacle['service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testShouldPassContainerAsParameter()
    {
        $barnacle = new Container();
        $barnacle['service'] = function () {
            return new Fixtures\Service();
        };
        $barnacle['container'] = function ($container) {
            return $container;
        };

        $this->assertNotSame($barnacle, $barnacle['service']);
        $this->assertSame($barnacle, $barnacle['container']);
    }

    public function testIsset()
    {
        $barnacle = new Container();
        $barnacle['param'] = 'value';
        $barnacle['service'] = function () {
            return new Fixtures\Service();
        };

        $barnacle['null'] = null;

        $this->assertTrue(isset($barnacle['param']));
        $this->assertTrue(isset($barnacle['service']));
        $this->assertTrue(isset($barnacle['null']));
        $this->assertFalse(isset($barnacle['non_existent']));
    }

    public function testConstructorInjection()
    {
        $params = array("param" => "value");
        $barnacle = new Container($params);

        $this->assertSame($params['param'], $barnacle['param']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $barnacle = new Container();
        echo $barnacle['foo'];
    }

    public function testOffsetGetHonorsNullValues()
    {
        $barnacle = new Container();
        $barnacle['foo'] = null;
        $this->assertNull($barnacle['foo']);
    }

    public function testUnset()
    {
        $barnacle = new Container();
        $barnacle['param'] = 'value';
        $barnacle['service'] = function () {
            return new Fixtures\Service();
        };

        unset($barnacle['param'], $barnacle['service']);
        $this->assertFalse(isset($barnacle['param']));
        $this->assertFalse(isset($barnacle['service']));
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testShare($service)
    {
        $barnacle = new Container();
        $barnacle['shared_service'] = $service;

        $serviceOne = $barnacle['shared_service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $barnacle['shared_service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testProtect($service)
    {
        $barnacle = new Container();
        $barnacle['protected'] = $barnacle->protect($service);

        $this->assertSame($service, $barnacle['protected']);
    }

    public function testGlobalFunctionNameAsParameterValue()
    {
        $barnacle = new Container();
        $barnacle['global_function'] = 'strlen';
        $this->assertSame('strlen', $barnacle['global_function']);
    }

    public function testRaw()
    {
        $barnacle = new Container();
        $barnacle['service'] = $definition = $barnacle->factory(function () { return 'foo'; });
        $this->assertSame($definition, $barnacle->raw('service'));
    }

    public function testRawHonorsNullValues()
    {
        $barnacle = new Container();
        $barnacle['foo'] = null;
        $this->assertNull($barnacle->raw('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testRawValidatesKeyIsPresent()
    {
        $barnacle = new Container();
        $barnacle->raw('foo');
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testExtend($service)
    {
        $barnacle = new Container();
        $barnacle['shared_service'] = function () {
            return new Fixtures\Service();
        };
        $barnacle['factory_service'] = $barnacle->factory(function () {
            return new Fixtures\Service();
        });

        $barnacle->extend('shared_service', $service);
        $serviceOne = $barnacle['shared_service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceOne);
        $serviceTwo = $barnacle['shared_service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceTwo);
        $this->assertSame($serviceOne, $serviceTwo);
        $this->assertSame($serviceOne->value, $serviceTwo->value);

        $barnacle->extend('factory_service', $service);
        $serviceOne = $barnacle['factory_service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceOne);
        $serviceTwo = $barnacle['factory_service'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceTwo);
        $this->assertNotSame($serviceOne, $serviceTwo);
        $this->assertNotSame($serviceOne->value, $serviceTwo->value);
    }

    public function testExtendDoesNotLeakWithFactories()
    {
        if (extension_loaded('barnacle')) {
            $this->markTestSkipped('Barnacle extension does not support this test');
        }
        $barnacle = new Container();

        $barnacle['foo'] = $barnacle->factory(function () { return; });
        $barnacle['foo'] = $barnacle->extend('foo', function ($foo, $barnacle) { return; });
        unset($barnacle['foo']);

        $p = new \ReflectionProperty($barnacle, 'values');
        $p->setAccessible(true);
        $this->assertEmpty($p->getValue($barnacle));

        $p = new \ReflectionProperty($barnacle, 'factories');
        $p->setAccessible(true);
        $this->assertCount(0, $p->getValue($barnacle));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testExtendValidatesKeyIsPresent()
    {
        $barnacle = new Container();
        $barnacle->extend('foo', function () {});
    }

    public function testKeys()
    {
        $barnacle = new Container();
        $barnacle['foo'] = 123;
        $barnacle['bar'] = 123;

        $this->assertEquals(array('foo', 'bar'), $barnacle->keys());
    }

    /** @test */
    public function settingAnInvokableObjectShouldTreatItAsFactory()
    {
        $barnacle = new Container();
        $barnacle['invokable'] = new Fixtures\Invokable();

        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $barnacle['invokable']);
    }

    /** @test */
    public function settingNonInvokableObjectShouldTreatItAsParameter()
    {
        $barnacle = new Container();
        $barnacle['non_invokable'] = new Fixtures\NonInvokable();

        $this->assertInstanceOf('Barnacle\Tests\Fixtures\NonInvokable', $barnacle['non_invokable']);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service definition is not a Closure or invokable object.
     */
    public function testFactoryFailsForInvalidServiceDefinitions($service)
    {
        $barnacle = new Container();
        $barnacle->factory($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callable is not a Closure or invokable object.
     */
    public function testProtectFailsForInvalidServiceDefinitions($service)
    {
        $barnacle = new Container();
        $barnacle->protect($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" does not contain an object definition.
     */
    public function testExtendFailsForKeysNotContainingServiceDefinitions($service)
    {
        $barnacle = new Container();
        $barnacle['foo'] = $service;
        $barnacle->extend('foo', function () {});
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extension service definition is not a Closure or invokable object.
     */
    public function testExtendFailsForInvalidServiceDefinitions($service)
    {
        $barnacle = new Container();
        $barnacle['foo'] = function () {};
        $barnacle->extend('foo', $service);
    }

    /**
     * Provider for invalid service definitions
     */
    public function badServiceDefinitionProvider()
    {
        return array(
          array(123),
          array(new Fixtures\NonInvokable())
        );
    }

    /**
     * Provider for service definitions
     */
    public function serviceDefinitionProvider()
    {
        return array(
            array(function ($value) {
                $service = new Fixtures\Service();
                $service->value = $value;

                return $service;
            }),
            array(new Fixtures\Invokable())
        );
    }

    public function testDefiningNewServiceAfterFreeze()
    {
        $barnacle = new Container();
        $barnacle['foo'] = function () {
            return 'foo';
        };
        $foo = $barnacle['foo'];

        $barnacle['bar'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $barnacle['bar']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot override frozen service "foo".
     */
    public function testOverridingServiceAfterFreeze()
    {
        $barnacle = new Container();
        $barnacle['foo'] = function () {
            return 'foo';
        };
        $foo = $barnacle['foo'];

        $barnacle['foo'] = function () {
            return 'bar';
        };
    }

    public function testRemovingServiceAfterFreeze()
    {
        $barnacle = new Container();
        $barnacle['foo'] = function () {
            return 'foo';
        };
        $foo = $barnacle['foo'];

        unset($barnacle['foo']);
        $barnacle['foo'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $barnacle['foo']);
    }

    public function testExtendingService()
    {
        $barnacle = new Container();
        $barnacle['foo'] = function () {
            return 'foo';
        };
        $barnacle['foo'] = $barnacle->extend('foo', function ($foo, $app) {
            return "$foo.bar";
        });
        $barnacle['foo'] = $barnacle->extend('foo', function ($foo, $app) {
            return "$foo.baz";
        });
        $this->assertSame('foo.bar.baz', $barnacle['foo']);
    }

    public function testExtendingServiceAfterOtherServiceFreeze()
    {
        $barnacle = new Container();
        $barnacle['foo'] = function () {
            return 'foo';
        };
        $barnacle['bar'] = function () {
            return 'bar';
        };
        $foo = $barnacle['foo'];

        $barnacle['bar'] = $barnacle->extend('bar', function ($bar, $app) {
            return "$bar.baz";
        });
        $this->assertSame('bar.baz', $barnacle['bar']);
    }
}
