<?php
function Miffie_autoload($class)
{
    $class = str_replace(array('_', '\\'), '/', $class);
    if (file_exists(__DIR__. '/src/' . $class . '.php')) {
        return include __DIR__. '/src/' . $class . '.php';
    }
}
spl_autoload_register("Miffie_autoload");
//require_once __DIR__.'/src/Miffie/Autoload.php';

Miffie\CLIRunner::run($argv);
