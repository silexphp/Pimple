Pimple
======

Pimple is a small dependency injection container for PHP 5.3 (less than 40
lines of code). You can read more information about dependency injection on my
[blog](http://fabien.potencier.org/article/11/what-is-dependency-injection).

Installation
------------

Pimple consists of just one file: `Pimple.php`. Download it, require it in
your code, and you're good to go:

    [php]
    require_once '/path/to/Pimple.php';

Usage
-----

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
