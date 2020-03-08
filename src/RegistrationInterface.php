<?php

namespace Barnacle;

use Barnacle\Container;

interface RegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c);
}