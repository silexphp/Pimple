Pimple
======

Pimple is a small dependency injection container for PHP 5.3 (less than 40
lines of code). You can read more information about dependency injection on my
[blog](http://fabien.potencier.org/article/11/what-is-dependency-injection).

Installation
------------

Pimple consists of just one file: `Pimple.php`. Download it, require it in
your code, and you're good to go:

    require_once '/path/to/Pimple.php';

Usage
-----

Obviously, the first step is to create a container:

    $container = new Pimple();

As many other dependency injection containers, Pimple is able to manage two
different kind of data: objects and parameters.

### Defining parameters

Defining a parameter is as simple as defining a new property of a Pimple
instance:

    // define some parameters
    $container->cookie_name = 'SESSION_ID';
    $container->storage_class = 'SessionStorage';

### Defining objects

Objects are defined by a lambda function that returns an instance of the
object:

    // define some objects
    $container->storage = function ($c)
    {
      return new $c->storage_class($c->cookie_name);
    };

    $container->user = function ($c)
    {
      return new User($c->storage);
    };

Notice that the lambda function has access to the current instance of the
container, allowing references to other objects or parameters.

As objects are only created when you get them, the order of the definitions
does not matter, and there is no performance penalty.

Using the defined objects is also very easy:

    // get the user object
    $user = $container->user;

    // the above call is roughly equivalent to the following code:
    // $storage = new SessionStorage('SESSION_ID');
    // $user = new User($storage);

By default, each time you get an object, Pimple returns a new instance of it.
If you want the same instance for all calls, wrap your lambda function with
the `asShared()` method:

    $c->user = $c->asShared(function ($c)
    {
      return new User($c->storage);
    });

### Packaging a container for reusability

If you use the same libraries over and over, you might want to create reusable
containers. Creating a reusable container is as simple as creating a class
that extends `Pimple`, and configuring it in the constructor:

    class SomeContainer extends Pimple
    {
      public function __construct()
      {
        $this->parameter = 'foo';
        $this->object = function () { return stdClass(); };
      }
    }

Here is the beginning of a container for the Zend Framework with the
configuration of the `Zend_Mail` component for sending emails from a Google
account:

    require_once __DIR__.'/lib/Pimple.php';

    class Zend_Pimple extends Pimple
    {
      public function __construct()
      {
        set_include_path(__DIR__.'/lib/vendor/Zend/library'.PATH_SEPARATOR.get_include_path());
        require_once 'Zend/Loader/Autoloader.php';
        spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));

        $this->mailer_transport = $this->asShared(function ($c)
        {
          return new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
            'auth'     => 'login', 'ssl' => 'ssl', 'port' => 465,
            'username' => $c->mailer_username,
            'password' => $c->mailer_password,
          ));
        });

        $this->mailer = $this->asShared(function ($c)
        {
          $mailer = new $c->mailer_class();
          $mailer->setDefaultTransport($c->mailer_transport);

          return $mailer;
        });

        $this->mailer_class = 'Zend_Mail';
      }
    }

Using this container from your own is rather easy:

    $container = new Pimple();

    // define your project parameters and objects
    // ...

    // embed the Zend_Pimple container
    $container->zend = $container->asShared(function () { return new Zend_Pimple(); });

    // configure it
    $container->zend->mailer_username = 'YourUsername';
    $container->zend->mailer_password = 'YourPassword';

    // use it
    $container->zend->mailer
      ->setBodyText('Sent from my Pimple container!')
      ->setFrom('fabien.potencier@example.com', 'Fabien Potencier')
      ->addTo('fabien.potencier@example.com', 'Fabien Potencier')
      ->setSubject('Sent from Pimple')
      ->send()
    ;

Links
-----

Documentation:
  http://github.com/fabpot/Pimple

Blog posts about Pimple:
  http://fabien.potencier.org/article/17/on-php-5-3-lambda-functions-and-closures

Source code:
  http://github.com/fabpot/Pimple

License
-------

Copyright (c) 2009 Fabien Potencier

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
