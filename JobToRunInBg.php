<?php
include "PhpBackgroundProcess.php";

$bgProc=new PhpBackgroundProcess();
$paramPassedInCallBackgroundProcessRun=$bgProc->ParseParam($argv);
//echo $paramPassedInCallBackgroundProcessRun;

//
// bellow code is sample for do business, remove and do you own
for($i=0;$i<5;$i++){
    $now= date("Y-m-d h:i:sa");
    $myfile = fopen("/var/www/html/PhpScripts/loop.txt", "a") or die("<h3 style='color:red'>Can not Loop</h3>");
    //$myfile = fopen("D:/loop.txt", "a") or die("<h3 style='color:red'>Can not Loop</h3>");
    $txt="\nLoop->Time: ".$now."\n";
    fwrite($myfile,$now. $txt);
    fwrite($myfile,$paramPassedInCallBackgroundProcessRun."\n"); 
    fclose($myfile);
    sleep(5);
}
// nohup php /var/www/html/PhpScripts/TestInfinityLoop.php name=du age=36 > /dev/null 2>&1
//pscp D:/CenterOnline/co_appmobinotify/Co.PhpLib/* root@112.78.3.199:/var/www/html

?>
