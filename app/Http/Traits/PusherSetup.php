<?php

namespace App\Http\Traits;

use App\Models\Notification;
use Pusher\Pusher;

trait PusherSetup
{
    /**
     * Push Notification by Pusher
     */
    public function pushNotificaion($channel, $event, $data, $clientuid, $message, $cid)
    {
        $options = array(
          'cluster' => env('PUSHER_APP_CLUSTER'),
          'useTLS' => true
        );
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $pusher->trigger($channel, $event, $data);

        $this->pushNotificaionNotify('ph-notify-channel-' . $clientuid, 'client-notify-event', $message, $clientuid, $message, $cid);
    }

    public function pushNotificaionNotify($channel, $event, $data, $clientuid, $message, $cid)
    {

        $insert_notification = Notification::create([
          'user_id' => $cid,
          'user_type' => 1,
          'event' => $event,
          'subject' => $channel,
          'message' => $message,
          'is_seen ' => 0
        ]);
        $options = array(
          'cluster' => env('PUSHER_APP_CLUSTER'),
          'useTLS' => true
        );
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
        $pusher->trigger($channel, $event, $data);
    }
}
