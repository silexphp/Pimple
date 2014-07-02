<?php

namespace Barnacle\Tests;

use Barnacle\Container;

/**
 * this be gettin' replaced with codeception tests
 */
class BarnacleServiceProviderInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $barnacle = new Container();

        $barnacleServiceProvider = new Fixtures\BarnacleServiceProvider();
        $barnacleServiceProvider->register($barnacle);

        $this->assertEquals('value', $barnacle['param']);
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $barnacle['service']);

        $serviceOne = $barnacle['factory'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $barnacle['factory'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testProviderWithRegisterMethod()
    {
        $barnacle = new Container();

        $barnacle->register(new Fixtures\BarnacleServiceProvider(), array(
            'anotherParameter' => 'anotherValue'
        ));

        $this->assertEquals('value', $barnacle['param']);
        $this->assertEquals('anotherValue', $barnacle['anotherParameter']);

        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $barnacle['service']);

        $serviceOne = $barnacle['factory'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $barnacle['factory'];
        $this->assertInstanceOf('Barnacle\Tests\Fixtures\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }
}
