<?php

namespace Barnacle;

use Barnacle\Exception\ContainerException;
use Barnacle\Exception\NotFoundException;
use Exception;
use Pimple\Container as Pimple;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 *  Class Container
 *  Pirate DIC
 *
 * @package Bone
 */
class Container extends Pimple implements ContainerInterface
{


    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if ($this->offsetExists($id)) {
            try {
                $item = $this->offsetGet($id);
                return $item;
            } catch (NotFoundException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new ContainerException($e->getMessage(), $e->getCode());
            }
        }

        throw new NotFoundException();
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

}
