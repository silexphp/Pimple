<?php

namespace Barnacle;

use Barnacle\Container;


interface ProviderInterface
{
    public function register(Container $container);
}