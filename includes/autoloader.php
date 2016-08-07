<?php
define("APP_PATH", str_replace(DIRECTORY_SEPARATOR, "/", dirname(__FILE__)));
spl_autoload_register(function($class) {
    $path = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $file = APP_PATH . "/libraries/{$path}.php";

    if (file_exists($file)) {
        require_once $file;
        return true;
    }
});
