<?php

//App name
define('APP_NAME', 'MVC Base');

//Database constants
define('DB_TYPE','mysql');
define('DB_URL','127.0.0.1');
define('DB_NAME','globalplanner');
define('DB_USER','root');
define('DB_PASS','');

// Url constants
define('URL', 'http://localhost/');

// Define libs folder
define('LIBS', 'libs/');

//Time values
define('ENDPOINT_TIME_FORMAT', "Y-m-d H:i:s");

// JWT
define('JWT_ISSUER', 'Projectgroep 13'); //issuer
define('JWT_NOT_USABLE_FOR_SECONDS', 5); //How long before a token starts working
define('JWT_USABLE_TIME_SECONDS', 3600); //How long the token works

// Keys
define('JWT_SECRET_TOKEN','hECyjvNinf6elrY');
