Pimple
======

Pimple is a small Dependency Injection Container for PHP 5.3 that consists
of just one file and one class (about 50 lines of code).

`Download it`_, require it in your code, and you're good to go::

    require_once '/path/to/Pimple.php';

Creating a container is a matter of instating the ``Pimple`` class::

    $container = new Pimple();

As many other dependency injection containers, Pimple is able to manage two
different kind of data: *objects* and *parameters*.

Defining Parameters
-------------------

Defining a parameter is as simple as using the Pimple instance as an array::

    // define some parameters
    $container['cookie_name'] = 'SESSION_ID';
    $container['session_storage_class'] = 'SessionStorage';

Defining Objects
----------------

Objects are defined by anonymous functions that return an instance of the
object::

    // define some objects
    $container['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

Notice that the anonymous function has access to the current container
instance, allowing references to other objects or parameters.

As objects are only created when you get them, the order of the definitions
does not matter, and there is no performance penalty.

Using the defined objects is also very easy::

    // get the session object
    $session = $container['session'];

    // the above call is roughly equivalent to the following code:
    // $storage = new SessionStorage('SESSION_ID');
    // $session = new Session($storage);

Passing Arguments to Objects
----------------------------

If the anonymous function requires arguments that aren't in the container,
wrap it with the ``factory()`` method. Call the object returned by the container
as you would a normal method and pass the desired values to it::

    // define the factory method
    $container['log.path'] = __DIR__ . '/logs/';
    $container['log'] = $container->factory(function ($c, $subsystem) {
        return new Logger($c['log.path'] . $subsystem . '.log');
    });

    // get the mail logger
    $log = $container['log']('mail');

Defining Shared Objects
-----------------------

By default, each time you get an object, Pimple returns a new instance of it.
If you want the same instance to be returned for all calls, wrap your
anonymous function with the ``share()`` method::

    $c['session'] = $c->share(function ($c) {
        return new Session($c['session_storage']);
    });

Protecting Parameters
---------------------

As Pimple makes no difference between a parameter and an object, you can use
the ``protect()`` method if you need to define a parameter as an anonymous
function::

    $c['random'] = $c->protect(function () { return rand(); });

Packaging a Container for reusability
-------------------------------------

If you use the same libraries over and over, you might want to create reusable
containers. Creating a reusable container is as simple as creating a class
that extends ``Pimple``, and configuring it in the constructor::

    class SomeContainer extends Pimple
    {
        public function __construct()
        {
            $this['parameter'] = 'foo';
            $this['object'] = function () { return stdClass(); };
        }
    }

Using this container from your own is as easy as it can get::

    $container = new Pimple();

    // define your project parameters and objects
    // ...

    // embed the SomeContainer container
    $container['embedded'] = $container->share(function () { return new SomeContainer(); });

    // configure it
    $container['embedded']['parameter'] = 'bar';

    // use it
    $container['embedded']['object']->...;

.. _Download it: https://github.com/fabpot/Pimple
