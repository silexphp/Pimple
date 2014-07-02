<?php


namespace Barnacle\Tests\Fixtures;

class Invokable
{
    public function __invoke($value = null)
    {
        $service = new Service();
        $service->value = $value;

        return $service;
    }
}
