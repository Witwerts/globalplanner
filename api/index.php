<?php

date_default_timezone_set("Europe/Amsterdam");

ini_set("allow_url_fopen", true);

//Require composer files
require 'vendor/autoload.php';

//Require config
require 'config.php';

//Autoload files
spl_autoload_register(function ($class) {
    include LIBS . $class . '.php';
});

//Instantiate App
$app = new Bootstrap();