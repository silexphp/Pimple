<?php

namespace Barnacle\Tests;

use Barnacle\Container;
use Barnacle\Exception\ContainerException;
use Barnacle\Exception\NotFoundException;
use DateTime;

class BarnacleTest extends \Codeception\TestCase\Test
{
    /** @var Container */
    protected $container;

    protected function _before()
    {
        $this->container = $c = new Container();
        $c[DateTime::class] = new DateTime('2020-04-06');
        $c['factory'] = $c->factory(function (Container $c){
            $date = $c->get(DateTime::class);

            return $date->format('D j M Y');
        });
    }

    protected function _after()
    {
        unset($this->container);
    }


    public function testBlah()
    {
        $c = $this->container;
        $this->assertTrue($c->has(DateTime::class));
        $this->assertTrue($c->has('factory'));
        $this->assertFalse($c->has('nonexistentKey'));
        $this->assertInstanceOf(DateTime::class, $c->get(DateTime::class));
        $this->assertEquals('Mon 6 Apr 2020', $c->get('factory'));
    }


    public function testNotFoundException()
    {
        $c = $this->container;
        $this->expectException(NotFoundException::class);
        $c->get('xxx');
    }


    public function testContainerxception()
    {
        $c = $this->container;
        $c['xxx'] = $c->factory(function(Container $c){
            throw new \Exception('i breaka your code!');
        });
        $this->expectException(ContainerException::class);
        $c->get('xxx');
    }
}


