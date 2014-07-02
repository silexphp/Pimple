<?php

namespace Barnacle;

use ArrayAccess;
use SplObjectStorage as Storage;
use Barnacle\ProviderInterface as Provider;
use Exception;

/**
 *  Class Container
 *  Pirate DIC
 *
 * @package Bone
 */
class Container implements ArrayAccess
{
    private $keys = array();
    private $values = array();
    private $factories;
    private $protected;
    private $frozen = array();
    private $raw = array();



    /**
     *  Be passin' yer variables in
     *
     * @param array $vars
     */
    public function __construct(array $vars = array())
    {
        $this->factories = new Storage();
        $this->protected = new Storage();
        foreach ($vars as $key => $value)
        {
            $this->offsetSet($key, $value);
        }
    }



    /**
     * Register yer service provider.
     *
     * @param Provider $provider
     * @param array  $values
     * @return $this
     */
    public function register(Provider $provider, array $values = array())
    {
        $provider->register($this);
        foreach ($values as $key => $value)
        {
            $this[$key] = $value;
        }
        return $this;
    }











    /**
     *  Ye can set yer variables here
     *
     * @param mixed $key
     * @param mixed $value
     * @throws Exception
     */
    public function offsetSet($key, $value)
    {
        if (isset($this->frozen[$key]))
        {
            throw new Exception("Overridin' be disabled for ".$key);
        }
        $this->values[$key] = $value;
        $this->keys[$key] = true;
    }











    /**
     * returns yer value ye want
     *
     * @param string $key
     * @return mixed The value of the parameter or an object
     * @throws Exception
     */
    public function offsetGet($key)
    {
        if (!isset($this->keys[$key]))
        {
            throw new Exception(sprintf('No key t\' return for '.$key));
        }

        if(isset($this->raw[$key]) || isset($this->protected[$this->values[$key]]) || !is_object($this->values[$key]) || !method_exists($this->values[$key], '__invoke'))
        {
            return $this->values[$key];
        }

        if (isset($this->factories[$this->values[$key]]))
        {
            return $this->values[$key]($this);
        }

        $this->frozen[$key] = true;
        $this->raw[$key] = $this->values[$key];

        return $this->values[$key] = $this->values[$key]($this);
    }












    /**
     *  Dubble check yer key is there
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->keys[$key]);
    }











    /**
     *  Keelhaul yer settin'
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        if (isset($this->keys[$key]))
        {
            if (is_object($this->values[$key]))
            {
                unset($this->factories[$this->values[$key]], $this->protected[$this->values[$key]]);
            }
            unset($this->values[$key], $this->frozen[$key], $this->raw[$key], $this->keys[$key]);
        }
    }















    /**
     *  Make yer callable into a factory
     *
     * @param callable $callable
     * @return callable
     * @throws Exception
     */
    public function addFactory($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke'))
        {
            throw new Exception("We be needin' closures or invokables");
        }

        $this->factories->attach($callable);

        return $callable;
    }












    /**
     *  Make yer callable a parameter and not a service.
     *
     * @param callable $callable
     * @return callable
     * @throws Exception
     */
    public function makeProtected($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke'))
        {
            throw new Exception('Garr! Callables should be closures or invokable');
        }
        $this->protected->attach($callable);
        return $callable;
    }














    /**
     * Gets yer uncooked parameter or object
     *
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getRaw($key)
    {
        if (!isset($this->keys[$key]))
        {
            throw new Exception($key.' not defined.');
        }
        if (isset($this->raw[$key]))
        {
            return $this->raw[$key];
        }
        return $this->values[$key];
    }





    /**
     *  An array of th' names of all th' keys on yer keyring
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->values);
    }











    /**
     * Extends yer object
     *
     * @param string $key
     * @param callable $callable
     * @return callable
     * @throws Exception
     */
    public function extend($key, $callable)
    {
        if (!isset($this->keys[$key]))
        {
            throw new Exception($key.' is not defined.');
        }
        if (!is_object($this->values[$key]) || !method_exists($this->values[$key], '__invoke'))
        {
            throw new Exception($key.' is nay object definition');
        }
        if (!is_object($callable) || !method_exists($callable, '__invoke'))
        {
            throw new Exception('That nay be a closure or invokable');
        }
        $factory = $this->values[$key];
        if(!is_callable($callable))
        {
            throw new Exception("That's not callable ya scurvy sea dog!");
        }
        else
        {
            $extended = function ($c) use ($callable, $factory)
            {
                return $callable($factory($c), $c);
            };
            if (isset($this->factories[$factory]))
            {
                $this->factories->detach($factory);
                $this->factories->attach($extended);
            }
            return $this[$key] = $extended;
        }


    }









}
