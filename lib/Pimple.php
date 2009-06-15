<?php

class Pimple
{
  protected $values = array();

  function __set($id, $value)
  {
    $this->values[$id] = $value;
  }

  function __get($id)
  {
    if (!isset($this->values[$id]))
    {
      throw new InvalidArgumentException(sprintf('Value "%s" is not defined.', $id));
    }

    return is_callable($this->values[$id]) ? $this->values[$id]($this) : $this->values[$id];
  }

  function asShared($callable)
  {
    return function ($c) use ($callable)
    {
      static $object;

      if (is_null($object))
      {
        $object = $callable($c);
      }

      return $object;
    };
  }
}

set_include_path('/Users/fabien/vendor/Zend/standard/library'.PATH_SEPARATOR.get_include_path());
require_once '/Users/fabien/vendor/Zend/standard/library/Zend/Loader.php';
Zend_Loader::registerAutoload();

$c = new Pimple();

// parameters
$c->mailer_class = 'Zend_Mail';
$c->mailer_username = 'fabien';
$c->mailer_password = 'myPass';

$c->foo = $c->asShared(function ($c)
{
  // some complex computation
  return 'foo';
});

// objects / services
$c->mailer_transport = function ($c)
{
  return new Zend_Mail_Transport_Smtp(
    'smtp.gmail.com',
    array(
      'auth'     => 'login',
      'username' => $c->mailer_username,
      'password' => $c->mailer_password,
      'ssl'      => 'ssl',
      'port'     => 465,
    )
  );
};
$c->mailer = $c->asShared(function ($c)
{
  $obj = new $c->mailer_class();
  $obj->setDefaultTransport($c->mailer_transport);

  return $obj;
});

print $c->foo."\n";

$mailer = $c->mailer;
//$mailer->setContent('Foo');
//$mailer->send();
/*
print spl_object_hash($c->mailer_transport)."\n";
print spl_object_hash($c->mailer_transport)."\n";
print spl_object_hash($c->mailer)."\n";
print spl_object_hash($c->mailer)."\n";
print spl_object_hash(new stdClass())."\n";
print spl_object_hash(new stdClass())."\n";
print spl_object_hash(new stdClass())."\n";
*/