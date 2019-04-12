<?php
include "RedisClient.php";


$redis = new RedisClient('127.0.0.1',6379);

$redis->Subscribe("topic_test",function($msg){
    echo "\n".$msg."\n";
  
      return true;
  });

?>
