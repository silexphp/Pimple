--TEST--
Test register()
--SKIPIF--
<?php if (!extension_loaded("pimple")) print "skip"; ?>
--FILE--
<?php

class Foo implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $p, array $options = array())
    {
        var_dump($p);
    }
}

$p = new Pimple\Container();
$p->register(new Foo, array(42 => 'bar'));

var_dump($p[42]);
--EXPECTF--
object(Pimple\Container)#1 (0) {
}
NULL