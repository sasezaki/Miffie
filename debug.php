<?php
function Miffie_autoload($class)
{
    $class = str_replace(array('_', '\\'), '/', $class);
    if (__DIR__. '/src/' . $class . '.php') {
        return include __DIR__. '/src/' . $class . '.php';
    }
}
spl_autoload_register("Miffie_autoload");

Miffie\CLIRunner::run($argv);
