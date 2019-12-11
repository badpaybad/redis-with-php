<?php

namespace App\Libs;

use App\Libs\RedisClient;
use Carbon\Carbon;

class EventListenerHelper
{

    private $redisHost;
    private $redisPort;
    private $redisPwd;
    private $redisDb;

    private $redis;

    public function __construct($redisHost, $redisPort, $redisPwd, $redisDb)
    {
        $this->redisHost = $redisHost;
        $this->redisPort = $redisPort;
        $this->redisPwd = $redisPwd;
        $this->redisDb = $redisDb;

        $this->redis  = new RedisClient($this->redisHost, $this->redisPort, $this->redisPwd, $this->redisDb);
    }

    public static function  BuildUserSessionChannel($tenantId, $userId, $subscriber)
    {
        return "CoWebNotification:UserOnline:" . $tenantId . "_" . $userId . "_" . $subscriber;
    }

    //when sigout done should call this
    public function ClearSession($tenantId, $userId)
    {

        $userOnlineKey = "CoWebNotification:UserOnline:" . $tenantId . "_" . $userId;


        return $this->redis->DeleteKey($userOnlineKey);
    }

    public function SendCallInfoToWeb($msg)
    {
        $channelKey = "CoWebNotification:ReceivedCall_All_Global";

        $this->redis->Enqueue($channelKey, $msg);
    }

    /**
     * web notify
     *
     * @param [type] $tenantId
     * @param [type] $userId
     * @param [type] $msgJsonString json_encode({"type":1 ,"datq":{..}})
     * @return void
     */
    public function SendNotifyToUser($tenantId, $userId, $msgJsonString)
    {
        $userOnlineKey = "CoWebNotification:UserOnline:" . $tenantId . "_" . $userId;
        $listSubscriber = $this->redis->ListRange($userOnlineKey);

        $listSubscriber = $this->GetAndRemoveSubscriberExpired($userOnlineKey, $listSubscriber);

        $uiChannelKey = [];
        foreach ($listSubscriber as $ck) {

            $uiChannelKey[] = $userOnlineKey . "_" . $ck;
        }

        $uiChannelKey = array_unique($uiChannelKey);

        foreach ($uiChannelKey as $ck) {
            $this->redis->Enqueue($ck, $msgJsonString);
        }
        return true;
    }

    private function GetAndRemoveSubscriberExpired($userOnlineKey, $listSubscriber = null)
    {
        if ($listSubscriber == null) {
            $listSubscriber = $this->redis->ListRange($userOnlineKey);
        }
        $result = [];
        foreach ($listSubscriber as $ck) {
            if (is_numeric($ck)) {
                $dateSubscribe = Carbon::createFromTimestamp(((int) $ck) / 1000);
                $dateDiff = Carbon::now()->diffInDays($dateSubscribe);

                if ($dateDiff >= 1) {
                    $this->redis->ListRemoveExisted($userOnlineKey, $ck);
                } else {
                    $result[] =  $ck;
                }
            } else {
                $result[] =  $ck;
            }
        }


        return array_unique($result);
    }

    private function SaveSessionChannel($uiChannelKey, &$userOnlineKey, &$subscriber)
    {
        $idx = strrpos($uiChannelKey, "_");
        $userOnlineKey = substr($uiChannelKey, 0, $idx);
        $subscriber = substr($uiChannelKey, $idx + 1);

        $this->redis->ListRemoveExisted($userOnlineKey, $subscriber);

        $this->redis->ListAdd($userOnlineKey, $subscriber, 86400);

        $this->GetAndRemoveSubscriberExpired($userOnlineKey);

    }

    public function LoopSendStream($channel = null)
    {
        $maxSleep = 30;
        $maxCounter = 25;

        // set_time_limit(100);
        set_time_limit(0);
        //https://www.php.net/manual/en/function.ignore-user-abort.php

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 0);

        while (@ob_end_flush());

        @ini_set('implicit_flush', 1);
        ob_implicit_flush(true);
        @ob_end_clean();

        ignore_user_abort(true);

        session_start();
        $_SESSION['latestRequestTime'] = time();
        session_write_close();

        header('Content-Type: text/event-stream;charset=UTF-8');
        header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
        header('X-Accel-Buffering: no'); //Nginx: unbuffered responses suitable for Comet and HTTP streaming applications
        //header("Access-Control-Allow-Origin: *");

        if ($channel == null || empty($channel)) {
            $channel = @$_GET['c'];
        }

        if (empty($channel)) {
            echo "data: {\"nochannel\":\"" . "true" . "\"}\n\n";
            //ob_flush();
            flush();
            exit();
            return;
        }

        $channel = urldecode($channel);

        $this->SaveSessionChannel($channel, $userOnlineKey, $subscriber);
        
        unset($this->redis);

        $lastEventId = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : 0);
        if ($lastEventId == 0) {
            $lastEventId = floatval(isset($_GET["lastEventId"]) ? $_GET["lastEventId"] : 0);
        }

        echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE
        echo "retry: 2000\n";

        while (true) {
            try {

                if (connection_aborted()) {
                    exit();
                    return;
                }
                //must instance and release 
                $redis  = new RedisClient($this->redisHost, $this->redisPort, $this->redisPwd, $this->redisDb);

                $msg = $redis->Dequeue($channel);

                if (!empty($msg) && $msg != "") {

                    echo "data: " . $msg . "\n\n";
                } else {
                    echo ": heartbeat\n\n";
                }

                flush();
                $redis = null;
                $msg = null;
                unset($redis);
                unset($msg);
            } catch (\Exception $ex) {
                echo "data: ERROR:" . $ex . "\n\n";
                flush();
            }

            sleep(1);
        }
    }
}

/* 
//java script
  PushServer = {
            listenChannel: function(channel, onMessageReceived, onConnected, onOpen) {
                var source = new EventSource('/eventlistener.php?c=' + encodeURIComponent(channel));
                source.onopen = function(evt) {
                    if (onOpen) onOpen(evt);
                };
                source.onconnected = function(evt) {
                    if (onConnected) onConnected(evt);
                    console.log('connected');
                };
                source.onmessage = function(evt) {
                    onMessageReceived(evt.data);
                    console.log('onmessage');
                    console.log(evt);
                };
                source.onerror = function(evt) {
                    if (evt.currentTarget.readyState == 2 || source.readyState == 2) {
                        PushServer.listenChannel(channel, onMessageReceived, onConnected, onOpen);
                        console.log('reconnected');
                        console.log('onerror');
                        console.log(evt);
                    }
                };
            }
        };

        //blade
          var channelKey = "<?php echo(App\Libs\EventListenerHelper::BuildUserSessionChannel( Auth::user()->tenant_id, Auth::user()->id, Carbon::now()->timestamp)) ?>";

            PushServer.listenChannel(channelKey, function(msg) {

                toastr.success(msg);
            });
 */
