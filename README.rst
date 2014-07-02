Barnacle
======

.. caution::

    This be th' documentation fer Barnacle, a pirate DIC plundered from th' ship fabpot/Pimple

Barnacle be a small Dependency Injection Container fer PHP.

Installation
------------

T' include Barnacle in yer project, add it to your ``composer.json`` file:

.. code-block:: javascript

    {
        "require": {
            "delboy1978uk/barnacle": "dev-mater"
        }
    }


Usage
-----

Creatin' a container is a matter of instantiating the ``Container`` class

.. code-block:: php

    use Barnacle\Container;

    $container = new Container();


As many other dependency injection containers, Barnacle be able to manage two
different kind of data: *services* and *parameters*.

Definin' Parameters
~~~~~~~~~~~~~~~~~~~

Definin' a parameter is as simple as using th' Barnacle instance as an array

.. code-block:: php

    // define some parameters
    $container['cookie_name'] = 'SESSION_ID';
    $container['session_storage_class'] = 'SessionStorage';

Definin' Services
~~~~~~~~~~~~~~~~~

A service be an object that does somethin' as part of a larger system.
Examples of services be: Database connection, templating engine, mailer. Almost
any object could be a service.

Services be defined by anonymous functions that return an instance of an
object

.. code-block:: php

    // define some services
    $container['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

Avast ye that th' anonymous function has access to the current container
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

Protectin' Parameters
~~~~~~~~~~~~~~~~~~~~~

Because Barnacle spies anonymous functions as service definitions, ye need t'
wrap anonymous functions with th' ``protect()`` method to store them as
parameter

.. code-block:: php

    $container['random'] = $container->protect(function () { return rand(); });

Modifyin' Services after Definition
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases ye may want to modify a service definition after it has been
defined. Ye can use the ``extend()`` method t' define additional code to
be run on yer service just after tis created

.. code-block:: php

    $container['mail'] = function ($c) {
        return new \Zend_Mail();
    };

    $container->extend('mail', function($mail, $c) {
        $mail->setFrom($c['mail.default_from']);

        return $mail;
    });

Th' first argument is th' name of th' object, th' second is a function that
gets access t' th' object instance and th' container.

Fetchin' the Service Creation Function
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When ye access an object, Barnacle automatically calls the anonymous function
that ye defined, which creates th' feckin' service object fer ye. If ye want to get
raw access t' this function, ye can use th' ``raw()`` method

.. code-block:: php

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

    $sessionFunction = $container->raw('session');

Extendin' a Container
~~~~~~~~~~~~~~~~~~~~~

If ye use th' same libraries over and over, ye might want to reuse some
services from one project to the other; package your services into a
**provider** by implementing ``Barnacle\ServiceProviderInterface``:

.. code-block:: php

    use Barnacle\Container;

    class FooProvider implements Barnacle\ServiceProviderInterface
    {
        public function register(Container $pimple)
        {
            // register some services and parameters
            // on $pimple
        }
    }

Then, th' provider can be easily registered on a Container:

.. code-block:: php

    $pimple->register(new FooProvider());

Definin' Factory Services
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, each time ye get a service, Barnacle returns th' **same instance**
of it. If ye want a different instance t' be returned for all calls, wrap your
anonymous function with th' ``factory()`` method

.. code-block:: php

    $container['session'] = $container->factory(function ($c) {
        return new Session($c['session_storage']);
    });


