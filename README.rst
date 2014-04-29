Pimple
======

.. caution::

    This is the documentation for Pimple 2.x. If you are using Pimple 1.x, read
    the `Pimple 1.x documentation`_. Reading the Pimple 1.x code is also a good
    way to learn more about how to create a simple Dependency Injection
    Container (Pimple 2.x implementation being more focused on performance).

Pimple is a small Dependency Injection Container for PHP that consists of just
one file and one class (about 80 lines of code).

`Download it`_, require it in your code, and you're good to go

.. code-block:: php

    require_once '/path/to/Pimple.php';

Creating a container is a matter of instating the ``Pimple`` class

.. code-block:: php

    $container = new Pimple();

As many other dependency injection containers, Pimple is able to manage two
different kind of data: *services* and *parameters*.

.. note::

    As of Pimple 2.1, you can also use the namespaced version of Pimple via the
    ``Pimple\Container`` class (``Pimple`` being just a deprecated alias.)

Defining Parameters
-------------------

Defining a parameter is as simple as using the Pimple instance as an array

.. code-block:: php

    // define some parameters
    $container['cookie_name'] = 'SESSION_ID';
    $container['session_storage_class'] = 'SessionStorage';

Defining Services
-----------------

A service is an object that does something as part of a larger system.
Examples of services: Database connection, templating engine, mailer. Almost
any object could be a service.

Services are defined by anonymous functions that return an instance of an
object

.. code-block:: php

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

Using the defined services is also very easy

.. code-block:: php

    // get the session object
    $session = $container['session'];

    // the above call is roughly equivalent to the following code:
    // $storage = new SessionStorage('SESSION_ID');
    // $session = new Session($storage);

Protecting Parameters
---------------------

Because Pimple sees anonymous functions as service definitions, you need to
wrap anonymous functions with the ``protect()`` method to store them as
parameter

.. code-block:: php

    $container['random'] = $container->protect(function () { return rand(); });

Modifying services after creation
---------------------------------

In some cases you may want to modify a service definition after it has been
defined. You can use the ``extend()`` method to define additional code to
be run on your service just after it is created

.. code-block:: php

    $container['mail'] = function ($c) {
        return new \Zend_Mail();
    };

    $container->extend('mail', function($mail, $c) {
        $mail->setFrom($c['mail.default_from']);

        return $mail;
    });

The first argument is the name of the object, the second is a function that
gets access to the object instance and the container.

Fetching the service creation function
--------------------------------------

When you access an object, Pimple automatically calls the anonymous function
that you defined, which creates the service object for you. If you want to get
raw access to this function, you can use the ``raw()`` method

.. code-block:: php

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

    $sessionFunction = $container->raw('session');

Extending a Container
---------------------

.. versionadded:: 2.1

    Support for extending a container was introduced in Pimple 2.1.

If you use the same libraries over and over, you might want to reuse some
services from one project to the other; package your services into a
**provider** by implementing ``Pimple\\ServiceProviderInterface``:

.. code-block:: php

    class FooProvider implements Pimple\ServiceProviderInterface
    {
        public function register(Container $pimple)
        {
            // register some services and parameters
            // on $pimple
        }
    }

Then, the provider can be easily registered on a Container:

    $pimple->register(new FooProvider());

Defining Factory Services
-------------------------

By default, each time you get a service, Pimple returns the **same instance**
of it. If you want a different instance to be returned for all calls, wrap your
anonymous function with the ``factory()`` method

.. code-block:: php

    $container['session'] = $container->factory(function ($c) {
        return new Session($c['session_storage']);
    });

.. _Download it:              https://raw2.github.com/fabpot/Pimple/master/lib/Pimple.php
.. _Pimple 1.x documentation: https://github.com/fabpot/Pimple/tree/1.1
