<?php

namespace Barnacle\Tests\Fixtures;

class NonInvokable
{
    public function __call($a, $b)
    {
    }
}
