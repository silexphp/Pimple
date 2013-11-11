Pimple
======

Pimple is a small Dependency Injection Container for PHP 5.3 that consists
of just one file and one class (about 80 lines of code).

`Download it`_, require it in your code, and you're good to go::

    require_once '/path/to/Pimple.php';

Creating a container is a matter of instating the ``Pimple`` class::

    $container = new Pimple();

As many other dependency injection containers, Pimple is able to manage two
different kind of data: *services* and *parameters*.

Defining Parameters
-------------------

Defining a parameter is as simple as using the Pimple instance as an array::

    // define some parameters
    $container['cookie_name'] = 'SESSION_ID';
    $container['session_storage_class'] = 'SessionStorage';

Defining Services
-----------------

A service is an object that does something as part of a larger system.
Examples of services: Database connection, templating engine, mailer. Almost
any object could be a service.

Services are defined by anonymous functions that return an instance of an
object::

    // define some services
    $container['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

Notice that the anonymous function has access to the current container
instance, allowing references to other services or parameters.

As objects are only created when you get them, the order of the definitions
does not matter, and there is no performance penalty.

Using the defined services is also very easy::

    // get the session object
    $session = $container['session'];

    // the above call is roughly equivalent to the following code:
    // $storage = new SessionStorage('SESSION_ID');
    // $session = new Session($storage);

Protecting Parameters
---------------------

Because Pimple sees anonymous functions as service definitions, you need to
wrap anonymous functions with the ``protect()`` method to store them as
parameter::

    $container['random'] = $container->protect(function () { return rand(); });

Modifying services after creation
---------------------------------

In some cases you may want to modify a service definition after it has been
defined. You can use the ``extend()`` method to define additional code to
be run on your service just after it is created::

    $container['mail'] = function ($c) {
        return new \Zend_Mail();
    };

    $container['mail'] = $container->extend('mail', function($mail, $c) {
        $mail->setFrom($c['mail.default_from']);
        return $mail;
    });

The first argument is the name of the object, the second is a function that
gets access to the object instance and the container. The return value is
a service definition, so you need to re-assign it on the container.

Fetching the service creation function
--------------------------------------

When you access an object, Pimple automatically calls the anonymous function
that you defined, which creates the service object for you. If you want to get
raw access to this function, you can use the ``raw()`` method::

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

    $sessionFunction = $container->raw('session');

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

    // define your project parameters and services
    // ...

    // embed the SomeContainer container
    $container['embedded'] = function () { return new SomeContainer(); };

    // configure it
    $container['embedded']['parameter'] = 'bar';

    // use it
    $container['embedded']['object']->...;

Defining Factory Services
-------------------------

By default, each time you get a service, Pimple returns the **same instance**
of it. If you want a different instance to be returned for all calls, wrap your
anonymous function with the ``factory()`` method::

    $container['session'] = $container->factory(function ($c) {
        return new Session($c['session_storage']);
    });

.. _Download it: https://github.com/fabpot/Pimple
