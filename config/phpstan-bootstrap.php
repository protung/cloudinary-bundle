<?php

/**
 * PSL maps its PSR-4 prefixes onto the directories holding its function files, so asking whether
 * "Psl\Env\get_var" is a class makes an autoloader include a file which only declares a function.
 * The class still does not exist, the next autoloader includes the very same file again, and PHP
 * dies with "Cannot redeclare Psl\Env\get_var()". Since 2.2.13 PHPStan performs such a lookup while
 * analysing, which kills its worker processes.
 *
 * Wrapping every registered autoloader stops the second include: a name which is already a defined
 * function can not be a class waiting to be autoloaded, so there is nothing left to look for.
 *
 * Taken from open-api-generator. Here it happens on PHP 8.3, with PSL 3.3 as well as 4.3, for example
 * for Psl\Env\get_var() and Psl\File\read(); PHP 8.5 is not affected. Remove this file together with
 * support for PHP 8.3, or once PHPStan runs on PHP 8.3 without it.
 */

declare(strict_types=1);

$autoloaders = spl_autoload_functions();

foreach ($autoloaders as $autoloader) {
    spl_autoload_unregister($autoloader);
}

foreach ($autoloaders as $autoloader) {
    spl_autoload_register(
        static function (string $name) use ($autoloader): void {
            if (function_exists($name)) {
                return;
            }

            $autoloader($name);
        },
    );
}
