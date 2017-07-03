<?php

namespace Pimple\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\ServiceLocator;

/**
 * ServiceLocator test case.
 *
 * @author Pascal Luna <skalpa@zetareticuli.org>
 */
class ServiceLocatorTest extends TestCase
{
    public function testCanAccessServices()
    {
        $pimple = new Container();
        $pimple['service'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('service'));

        $this->assertSame($pimple['service'], $locator->get('service'));
    }

    public function testCanAccessAliasedServices()
    {
        $pimple = new Container();
        $pimple['service'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('alias' => 'service'));

        $this->assertSame($pimple['service'], $locator->get('alias'));
    }

    /**
     * @expectedException \Pimple\Exception\UnknownIdentifierException
     * @expectedExceptionMessage Identifier "service" is not defined.
     */
    public function testCannotAccessAliasedServicesUsingRealIdentifier()
    {
        $pimple = new Container();
        $pimple['service'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('alias' => 'service'));

        $service = $locator->get('service');
    }

    /**
     * @expectedException \Pimple\Exception\UnknownIdentifierException
     * @expectedExceptionMessage Identifier "service" is not defined.
     */
    public function testGetValidatesServiceCanBeLocated()
    {
        $pimple = new Container();
        $pimple['service'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('foo'));

        $service = $locator->get('service');
    }

    public function testHasValidatesServiceCanBeLocated()
    {
        $pimple = new Container();
        $pimple['service1'] = function () {
            return new Fixtures\Service();
        };
        $pimple['service2'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('service1'));

        $this->assertTrue($locator->has('service1'));
        $this->assertFalse($locator->has('service2'));
    }

    public function testHasDoesNotCheckIfServiceExists()
    {
        $pimple = new Container();
        $pimple['service'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('foo' => 'service', 'bar' => 'invalid'));

        $this->assertTrue($locator->has('foo'));
        $this->assertTrue($locator->has('bar'));
    }

    public function testCount()
    {
        $pimple = new Container();

        $locator = new ServiceLocator($pimple, array('service1', 'service2', 'service3'));

        $this->assertSame(3, count($locator));
    }

    public function testIsIterable()
    {
        $pimple = new Container();
        $pimple['service1'] = function () {
            return new Fixtures\Service();
        };
        $pimple['service2'] = function () {
            return new Fixtures\Service();
        };

        $locator = new ServiceLocator($pimple, array('service1', 'service2'));

        $this->assertSame(array('service1' => $pimple['service1'], 'service2' => $pimple['service2']), iterator_to_array($locator));
    }
}
