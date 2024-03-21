<?php

namespace Barnacle\Tests;

use Barnacle\Container;
use Barnacle\Exception\ContainerException;
use Barnacle\Exception\NotFoundException;
use Codeception\Test\Unit;
use DateTime;

class BarnacleTest extends Unit
{
    protected Container $container;

    protected function _before(): void
    {
        $this->container = $c = new Container();
        $c[DateTime::class] = new DateTime('2020-04-06');
        $c['factory'] = $c->factory(function (Container $c){
            $date = $c->get(DateTime::class);

            return $date->format('D j M Y');
        });
    }

    protected function _after(): void
    {
        unset($this->container);
    }


    public function testBlah(): void
    {
        $c = $this->container;
        $this->assertTrue($c->has(DateTime::class));
        $this->assertTrue($c->has('factory'));
        $this->assertFalse($c->has('nonexistentKey'));
        $this->assertInstanceOf(DateTime::class, $c->get(DateTime::class));
        $this->assertEquals('Mon 6 Apr 2020', $c->get('factory'));
    }


    public function testNotFoundException(): void
    {
        $c = $this->container;
        $this->expectException(NotFoundException::class);
        $c->get('xxx');
    }


    public function testContainerxception(): void
    {
        $c = $this->container;
        $c['xxx'] = $c->factory(function(Container $c){
            throw new \Exception('i breaka your code!');
        });
        $this->expectException(ContainerException::class);
        $c->get('xxx');
    }
}


