<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once __ROOT__ . '/vendor/autoload.php';

use App\Libs\EventListenerHelper;

$dotenv = new \Dotenv\Dotenv(__ROOT__);
$dotenv->load();

 function value_custom($value)
 {
     return $value instanceof Closure ? $value() : $value;
 }
 function env_custom($key, $default = null)
 {
     $value = getenv($key);

     if ($value === false) {
         return value_custom($default);
     }

     switch (strtolower($value)) {
         case 'true':
         case '(true)':
             return true;
         case 'false':
         case '(false)':
             return false;
         case 'empty':
         case '(empty)':
             return '';
         case 'null':
         case '(null)':
             return;
     }

     if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
         return substr($value, 1, -1);
     }

     return $value;
 }

define('__REDISHOST__', env_custom('REDIS_HOST'));
define('__REDISPORT__', env_custom('REDIS_PORT'));
define('__REDISPWD__', env_custom('REDIS_PASSWORD'));
define('__REDISDB__', env_custom('REDIS_NOTI_DB'));

$sse = new EventListenerHelper(__REDISHOST__, __REDISPORT__, __REDISPWD__, __REDISDB__);

$sse->LoopSendStream();
