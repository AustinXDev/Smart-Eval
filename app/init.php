<?php

//avoid writing long paths
define('ROOT_PATH', dirname(__DIR__));

session_start();
require_once ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

?>