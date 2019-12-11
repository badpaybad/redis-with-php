PushServer = {
            __subscribers: [],
            __handlers: [],
            init: function() {
                PushServer.__handlers = [];
                PushServer.__subscribers = [];
            },
            addHandler: function(handleNotification, mainChannel, subscriberName) {

                var channelKey = mainChannel + "_" + subscriberName;

                PushServer.__subscribers[channelKey] = channelKey;

                PushServer.__handlers[channelKey] = handleNotification;

                console.log("Subscribe: " + channelKey);

                PushServer.listenChannel(channelKey, function(msg) {
                    // handleNotification(msg);
                    PushServer.__handlers[channelKey](msg);
                });

            },

            removeHandler: function(subscriberName) {

                PushServer.__subscribers = PushServer.__subscribers.filter(function(item, k) {
                    return k !== subscriberName
                });
                PushServer.__handlers = PushServer.__subscribers.filter(function(item, k) {
                    return k !== subscriberName
                });
            },

            listenChannel: function(channel, onMessageReceived, onConnected, onOpen) {
                var source = new EventSource('/eventlistener.php?c=' + encodeURIComponent(channel));
                source.onopen = function(evt) {
                    if (onOpen) onOpen(evt);
                };
                source.onconnected = function(evt) {
                    if (onConnected) onConnected(evt);
                    // console.log('connected');
                };
                source.onmessage = function(evt) {

                    onMessageReceived(evt.data);

                    // var obj = evt.data;

                    // for (var k in PushServer.__handlers) {
                    //     PushServer.__handlers[k](obj);
                    // }
                    //console.log('onmessage');
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

        PushServer.init();  
        
          PushServer.addHandler(function(msg) {
                
        msg= JSON.parse(msg);
        WebSystemNotification.showToastr(msg);

    }, "CoWebNotification:UserOnline:<?php echo \Auth::user()->tenant_id ?>_" + '<?php echo \Auth::user()->id?>', 
    new Date().getTime()
   
    );
