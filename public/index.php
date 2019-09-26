<?php

define('ROOT_PATH', dirname(__DIR__));

require (ROOT_PATH . '/vendor/autoload.php');

header("Content-Type: application/json");

$env = Dotenv\Dotenv::create(ROOT_PATH);
$env->load();

$app = new App\AgentBot;
echo $app->run();
