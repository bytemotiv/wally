<?php

require "vendor/autoload.php";

// --- load .env file
function loadEnv($filePath) {
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), "#") === 0 || empty($line)) {
                continue;
            }
            list($key, $value) = explode("=", $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

loadEnv(__DIR__."/.env");


// --- init framework
$f3 = \Base::instance();

$f3->config(__DIR__."/config/config.ini");
$f3->config(__DIR__."/config/routes.ini");

$f3->run();

?>