<?php
/**
 * Print out opcache configuration.
 */
function printOpcacheConfiguration()
{
    echo 'Opcache configuration:'.PHP_EOL;
    print_r(opcache_get_configuration());
}

/**
 * Print out opcache status.
 */
function printOpcacheStatus()
{
    echo 'Opcache status:'.PHP_EOL;
    print_r(opcache_get_status());
}

echo 'Before clearing opcache: '.PHP_EOL;
printOpcacheConfiguration();
//printOpcacheStatus();
echo PHP_EOL;
echo 'Clearing opcache.'.PHP_EOL;
opcache_reset();
echo 'Opcache cleared.'.PHP_EOL;
echo PHP_EOL;
printOpcacheStatus();