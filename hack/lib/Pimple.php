<?hh

/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2009 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Pimple main class.
 *
 * @author  Fabien Potencier
 * @author  Máximo Cuadros <mcuadros@gmail.com>
 */

type Factory = (function (Pimple): mixed);
type Extendable = (function (mixed, Pimple): mixed);

class Pimple implements ArrayAccess<string, mixed>
{
    private Set<Factory> $factories = Set {};
    private Set<Factory> $protected = Set {};
    private Set<string> $frozen = Set {};
    private Set<string> $keys = Set {};
    private Map<string, mixed> $values = Map {};
    private Map<string, Factory> $closures = Map {};

    /**
     * Instantiate the container.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array<string, mixed> $values = [])
    {
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same name as an existing parameter would break your container).
     *
     * @param  string           $id    The unique identifier for the parameter or object
     * @param  mixed            $value The value of the parameter or a closure to define an object
     *
     * @return Pimple
     *
     * @throws RuntimeException Prevent override of a frozen service
     */
    public function offsetSet($id, $value): this
    {
        if ($this->frozen->contains($id)) {
            throw new RuntimeException(sprintf('Cannot override frozen service "%s".', $id));
        }

        if (is_object($value) && is_callable($value, '__invoke')) {
            $this->closures->add(Pair{$id, $value});
        } else {
            $this->values->add(Pair{$id, $value});
        }

        $this->keys->add($id);

        return $this;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($id): mixed
    {
        if (!$this->keys->contains($id)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        if ($this->values->containsKey($id)) {
           return $this->values->get($id);
        }

        $closure = $this->closures->at($id);
        if ($this->protected->contains(spl_object_hash($closure))) {
            return $closure;
        }

        $value = $closure($this);
        if (!$this->factories->contains(spl_object_hash($closure))) {
            $this->frozen->add($id);
            $this->values->add(Pair{$id, $value});
        }

        return $value;
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function offsetExists($id): bool
    {
        return $this->keys->contains($id);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return Pimple
     */
    public function offsetUnset($id): this
    {
        if (!$this->keys->contains($id)) {
            return $this;
        }

        $closure = $this->closures->get($id);
        if ($closure) {
            $hash = spl_object_hash($closure);
            $this->factories->remove($hash);
            $this->protected->remove($hash);
        }

        $this->closures->removeKey($id);
        $this->values->removeKey($id);
        $this->frozen->remove($id);
        $this->keys->remove($id);

        return $this;
    }

    /**
     * Marks a callable as being a factory service.
     *
     * @param callable $callable A service definition to be used as a factory
     *
     * @return Factory The passed callable
     *
     * @throws InvalidArgumentException Service definition has to be a closure of an invokable object
     */
    public function factory(Factory $callable): Factory
    {
        $this->factories->add(spl_object_hash($callable));

        return $callable;
    }

    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param callable $callable A callable to protect from being evaluated
     *
     * @return Factory The passed callable
     *
     * @throws InvalidArgumentException Service definition has to be a closure of an invokable object
     */
    public function protect(Factory $callable): Factory 
    {
        $this->protected->add(spl_object_hash($callable));

        return $callable;
    }

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function raw(string $id)
    {
        if (!$this->keys->contains($id)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->closures->get($id);
    }

    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string   $id       The unique identifier for the object
     * @param callable $callable A service definition to extend the original
     *
     * @return Factory The wrapped callable
     *
     * @throws InvalidArgumentException if the identifier is not defined or not a service definition
     */
    public function extend(string $id, Extendable $callable): Factory
    {
        if (!$this->keys->contains($id)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        if (!$this->closures->containsKey($id)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }

        $factory = $this->closures->at($id);
        $extended = $c ==> $callable($factory($c), $c);
     
        $hash = spl_object_hash($factory);
        if ($this->factories->contains($hash)) {
            $this->factories->remove(spl_object_hash($factory));
            $this->factories->add(spl_object_hash($extended));
        }

        $this->closures->add(Pair{$id, $extended});

        return $extended;
    }

    /**
     * Returns all defined value names.
     *
     * @return Vector<string> A vector of value names
     */
    public function keys(): Vector<string>
    {
        return $this->values->keys();
    }
}