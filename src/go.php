<?php

set_time_limit ( 30 );                  // meio minuto
date_default_timezone_set ( 'UTC' );    // set regiao
ignore_user_abort ( true );             // rodar mesmo depois que acabar 

header ( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); 
header ( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
header ( 'Cache-Control: post-check=0, pre-check=0', FALSE ); 
header ( 'Pragma: no-cache' );

//Load Depends from Composer...
require ( __DIR__.'/../vendor/autoload.php' );

require_once ( 'live/Archive.php' );
require_once ( 'live/Alive.php' );

use InstagramAPI\Instagram;
use InstagramAPI\Request\Live;

$debug = false; 
$truncatedDebug = false;

\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
$ig = new Instagram($debug, $truncatedDebug);

Alive::$id = ID;
Alive::$user = IG_USERNAME;
Alive::$password = IG_PASS;

Alive::login($ig);
Alive::broadcast($ig); 