Pimple
======

Pimple is a small dependency injection container for PHP 5.3 (less than 40
lines of code). You can read more information about dependency injection on my
[blog](http://fabien.potencier.org/article/11/what-is-dependency-injection).

As many other dependency injection containers, Pimple is able to manage two
different kind of data: objects and parameters.

Defining a parameter is as simple as defining a new property of a Pimple
instance:

    $container = new Pimple();

    // define some parameters
    $container->cookie_name = 'SESSION_ID';
    $container->storage_class = 'SessionStorage';

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

    [php]
    $c->user = $c->asShared(function ($c)
    {
      return new User($c->storage);
    });

More information can be found on my personal
[blog](http://fabien.potencier.org/article/17/on-php-5-3-lambda-functions-and-closures).
