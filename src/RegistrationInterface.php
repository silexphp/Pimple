<?php

namespace Barnacle;

use Barnacle\Container;

interface RegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c);

    /**
     * @return string
     */
    function getEntityPath(): string;

    /**
     * @return bool
     */
    function hasEntityPath(): bool;
}