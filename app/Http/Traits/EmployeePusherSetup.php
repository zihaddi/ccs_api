<?php

namespace App\Http\Controllers\Traits;

use App\Models\NotificationEmployee;
use Pusher\Pusher;

trait EmployeePusherSetup
{

  /**
   * Push Notification by Pusher
   */
  public function pushNotificaionEmp($channel,$event,$data,$emp,$message,$eid) {
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

    $this->pushNotificaionNotifyEmp('ph-notify-emp-channel-' . $emp, 'emp-notify-event', $message,$emp,$message,$emp);
  }

  public function pushNotificaionNotifyEmp($channel,$event,$data,$clientuid,$message,$emp) {

    $insert_notification=NotificationEmployee::create([
      'user_id' => $emp,
      'user_type' => 1,
      'event' => $event,                
      'subject' => $channel,  
      'message' =>$message,    
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
