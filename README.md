# redis-with-php
Provide some utitlities function to working with redis on php. Folder /redis for sample pubsub usage. Base on PhpBackgroundProcess.php we can do php work like multi thread. we can call any subscribe.php to run as linux process stand alone and without block current thread of php. 

#### ref: https://github.com/ziogas/PHP-Redis-implementation 
 
## provide function to working with cache : Get, Set, HashGet, HashSet, HashGetAll , Existed
## provide function to working with queue: Dequeue, Enqueue
## provide function to working with stack: StackPush, StackPop
## provide function to pub/sub mechanism: Publish, Subscribe, Unsubscribe
### Subscribe should run stand alone process
eg: php redis/subscribe.php
## Can call php to run in background by PhpBackgroundProcess.php 
        $bgProc=new PhpBackgroundProcess();
        $bgProc->Run($rootDir."/JobToRunInBg.php", "{name:du, age:35}");
         
