<?php
include "RedisClient.php";


$redis = new RedisClient('127.0.0.1',6379);
while(1){
    $res=$redis->Publish("topic_test","msg test ".date(DATE_W3C));
   
    foreach($res as $r){
        echo "\n".$r;
    }
    usleep(1000000);
}

?>
