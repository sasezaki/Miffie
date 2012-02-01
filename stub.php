#!/usr/bin/env php
<?php
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process Miffie phar:" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit -1;
}
function Miffie_autoload($class)
{
    $class = str_replace(array('_', '\\'), '/', $class);
    if (file_exists('phar://' . __FILE__ . '/Miffie-@PACKAGE_VERSION@/php/' . $class . '.php')) {
        return include 'phar://' . __FILE__ . '/Miffie-@PACKAGE_VERSION@/php/' . $class . '.php';
    }
}
spl_autoload_register("Miffie_autoload");
$phar = new Phar(__FILE__);
$sig  = $phar->getSignature();
define('Miffie_SIG', $sig['hash']);
define('Miffie_SIGTYPE', $sig['hash_type']);

if (PHP_SAPI == 'cli') {
    Miffie\CLIRunner::run($argv);
}

__HALT_COMPILER();
