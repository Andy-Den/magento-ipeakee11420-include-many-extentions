<?php
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') 
{
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    if (count($ips) > 1 && $ips[0] == '127.0.0.1') 
    {
        $_SERVER['REMOTE_ADDR'] = $ips[1];
    }
    else 
    {
        $_SERVER['REMOTE_ADDR'] = $ips[0];
    }
}
if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 80;
}

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO_NEW']) && $_SERVER['HTTP_X_FORWARDED_PROTO_NEW'] == 'https') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 80;
}


if (file_exists('/home/www/xhprof/external/header.php')) {
    require_once('/home/www/xhprof/external/header.php');
}

