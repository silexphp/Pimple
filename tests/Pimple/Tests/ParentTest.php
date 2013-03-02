<?php
namespace Pimple\Tests;

use Pimple;

/**
 * Parent pimple tests.
 *
 * @package pimple
 * @author Mauro Franceschini <mauro.franceschini@gmail.com>
 */
class ParentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pimple
     */
    private $parent;
    /**
     * @var Pimple
     */
    private $fixture;

    public function setUp()
    {
        $this->parent = new Pimple();
        $this->parent['param1'] = 'value1';
        $this->parent['param2'] = 'value2';
        $this->parent['service1'] = function () {
            return new Service();
        };

        $this->fixture = new Pimple($this->parent);
        $this->fixture['param2'] = 'masked';
        $this->fixture['param3'] = 'new';
    }

    public function testOffsetGet()
    {
        $this->assertEquals('masked', $this->fixture['param2']);
        $this->assertEquals('value1', $this->fixture['param1']);
    }

    public function testIsset()
    {
        $this->assertTrue(isset($this->fixture['param2']));
        $this->assertTrue(isset($this->fixture['param1']));
    }

    public function testRaw()
    {
        $this->parent['service2'] = $definition = function () { return 'foo'; };
        $this->assertSame($definition, $this->fixture->raw('service2'));
    }


    public function testExtend()
    {
        $this->parent['shared_service'] = $this->parent->share(function () {
            return new Service();
        });

        $value = 12345;

        $this->fixture->extend('shared_service', function($sharedService) use ($value) {
            $sharedService->value = $value;

            return $sharedService;
        });

        $serviceOne = $this->fixture['shared_service'];
        $this->assertInstanceOf('Pimple\Tests\Service', $serviceOne);
        $this->assertEquals($value, $serviceOne->value);

        $serviceTwo = $this->fixture['shared_service'];
        $this->assertInstanceOf('Pimple\Tests\Service', $serviceTwo);
        $this->assertEquals($value, $serviceTwo->value);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    public function testKeys()
    {
        $this->assertEquals(array('param1', 'param2', 'param3', 'service1'), $this->fixture->keys());
    }

}
