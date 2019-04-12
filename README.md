# redis-with-php
folder /redis for sample pubsub usage
## provide function to working with cache : Get, Set, HashGet, HashSet, HashGetAll 
## provide function to working with queue: Dequeue, Enqueue
## provide function to pub/sub mechanism: Publish, Subscribe, Unsubscribe
### Subscribe should run stand alone process
eg: php redis/subscribe.php
## Can call php to run in background by PhpBackgroundProcess.php 
        $bgProc=new PhpBackgroundProcess();
        $bgProc->Run($rootDir."/JobToRunInBg.php", "{name:du, age:35}");
   
        
