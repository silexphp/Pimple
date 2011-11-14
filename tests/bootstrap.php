<?php

/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'Pimple\\Tests')) {
        $path = implode('/', explode('\\', $class)).'.php';
        require_once __DIR__.'/'.$path;
        return true;
    }
});

if (file_exists($file = __DIR__.'/../autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/../autoload.php.dist')) {
    require_once $file;
}
