Pimple
======

.. 注意::
	这是Pimple 3.x的文档。如果您使用Pimple 1.x，请阅读`Pimple 1.x documentation`_文档。阅读Pimple 1.x代码也是了解更多关于如何创建简单的依赖注入容器（Pimple的更新版本更侧重于性能）的好方法。

Pimple是一个简单的PHP依赖注入容器(Dependency Injection Container)。

安装
------------
 
在您的项目中使用Pimple之前，将其添加到您的 ``composer.json`` 文件中：

.. code-block:: bash

    $ ./composer.phar require pimple/pimple "~3.0"

或者，Pimple也可以作为PHP C扩展：

.. code-block:: bash

    $ git clone https://github.com/silexphp/Pimple
    $ cd Pimple/ext/pimple
    $ phpize
    $ ./configure
    $ make
    $ make install

用法
-----

创建一个 ``Container`` 实例：

.. code-block:: php

    use Pimple\Container;

    $container = new Container();

与许多其他依赖注入容器一样，Pimple管理两种不同类型的数据： **services** 和 **parameters** 。


定义服务
~~~~~~~~~~~~~~~~~

服务是作为更大系统一部分的对象。服务示例：数据库连接、模板引擎或邮件程序。几乎任何全局对象都可以看作是一个服务。

服务由匿名函数定义，返回一个对象实例：

.. code-block:: php

    // define some services
    $container['session_storage'] = function ($c) {
        return new SessionStorage('SESSION_ID');
    };

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

需要注意的是匿名函数可以访问当前容器实例，允许引用其他服务或参数。

由于对象仅在获取对象时创建，所以定义的顺序并不重要。

使用已经定义的服务也很容易：

.. code-block:: php

    // get the session object
    $session = $container['session'];

    // the above call is roughly equivalent to the following code:
    // $storage = new SessionStorage('SESSION_ID');
    // $session = new Session($storage);

定义工厂服务
~~~~~~~~~~~~~~~~~~~~~~~~~

默认情况下，每次获得服务时，Pimple返回相同的实例 。如果要为所有调用返回不同的实例，请使用该 ``factory()`` 方法包装匿名函数：

.. code-block:: php

    $container['session'] = $container->factory(function ($c) {
        return new Session($c['session_storage']);
    });

	
现在，每次调用 ``$container['session']`` 返回会话的新实例。


定义参数
~~~~~~~~~~~~~~~~~~~

定义一个参数允许从外部简化容器的配置并存储全局值：

.. code-block:: php

    // define some parameters
    $container['cookie_name'] = 'SESSION_ID';
    $container['session_storage_class'] = 'SessionStorage';

如果您需要更改``session_storage``服务定义可以参考下面代码：

.. code-block:: php

    $container['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

 
现在可以通过覆盖 ``session_storage_class`` 参数来轻松地更改cookie名称， 而不是重新定义服务定义。

保护参数
~~~~~~~~~~~~~~~~~~~~~

因为Pimple将匿名函数视为服务定义，所以您需要使用 ``protect()`` 方法将匿名函数包装为参数：

.. code-block:: php

    $container['random_func'] = $container->protect(function () {
        return rand();
    });

定义后修改服务
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

在某些情况下，您可能希望在定义服务定义后修改服务定义。您可以使用 ``extend()`` 方法在创建服务之后定义要运行的其他代码：

.. code-block:: php

    $container['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

    $container->extend('session_storage', function ($storage, $c) {
        $storage->...();

        return $storage;
    });

The first argument is the name of the service to extend, the second a function
that gets access to the object instance and the container.

第一个参数是要扩展的服务的名称，第二个参数是访问对象实例和容器的函数。

扩展容器
~~~~~~~~~~~~~~~~~~~~~

如果您一遍又一遍地使用相同的库，您可能希望将一个项目中的一些服务重用到下一个项目; 通过实现接口 ``Pimple\ServiceProviderInterface`` ，可以将您的服务打包为一个服务提供者( **provider** ) :

.. code-block:: php

    use Pimple\Container;

    class FooProvider implements Pimple\ServiceProviderInterface
    {
        public function register(Container $pimple)
        {
            // register some services and parameters
            // on $pimple
        }
    }

然后，在容器上注册服务提供者：

.. code-block:: php

    $pimple->register(new FooProvider());

获取服务创建功能
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

当您访问对象时，Pimple会自动调用您定义的匿名函数，这将为您创建服务对象。如果您想要访问此功能的原始访问权限，可以使用以下 ``raw()`` 方法：

.. code-block:: php

    $container['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

    $sessionFunction = $container->raw('session');

.. _Pimple 1.x documentation: https://github.com/silexphp/Pimple/tree/1.1
