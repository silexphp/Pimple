<?php

namespace Barnacle\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ContainerException extends Exception implements NotFoundExceptionInterface
{

}
