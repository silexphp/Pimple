<?php

namespace Barnacle\Tests\Fixtures;

use Barnacle\Container;
use Barnacle\ProviderInterface;

class BarnacleServiceProvider implements ProviderInterface
{
    /**
     * Registers services on th' given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $barnacle An Container instance
     */
    public function register(Container $barnacle)
    {
        $barnacle['param'] = 'value';

        $barnacle['service'] = function () {
            return new Service();
        };

        $barnacle['factory'] = $barnacle->factory(function () {
            return new Service();
        });
    }
}
