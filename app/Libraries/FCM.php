<?php
namespace App\Libraries;

use CodeIgniter\Library;

class FCM
{
    protected $API_KEY  = 'AAAAEfmQP4c:APA91bHYDEPVlLgD0W0eQ7nBFXwVPslzKkhhho8vC6H9FDp9bCgcRXctX9jtzg1NSwNBH8qbuBZSQQyQUUqbUUo8ZCHAQMMRRrtW3I4YL5FBxnZufm7rnKj48vtW2xRTx35hW0KiMYwo';
    protected $SENDER_ID = '77201424263'; 

    public function doRequest($url, $fields, $headers, $method) {
        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($method=="POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        }
        // Execute Post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            $result = array("error" => curl_error($ch));
        } else {
            $result = json_decode($result, true);
        }
        // Close Connection
        curl_close($ch);
        return $result;
    }

    public function sendmsg($registatoin_ids, $message) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'delay_while_idle' => false,
            'registration_ids' => $registatoin_ids,
            'data'             => $message
        );
        $headers = array(
            "Authorization: key=".$this->API_KEY,
            "Content-Type: application/json"
        );
        return $this->doRequest($url, $fields, $headers, "POST");
    }

    public function sendgroupmsg($registatoin_ids, $message) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'delay_while_idle' => false,
            'to'               => $registatoin_ids,
            'data'             => $message
        );
        $headers = array(
            "Authorization: key=".$this->API_KEY,
            "Content-Type: application/json"
        );
        return $this->doRequest($url, $fields, $headers, "POST");
    }

    public function creategroup($registatoin_ids, $notification_group_name) {
        $url = 'https://fcm.googleapis.com/fcm/notification';
        $fields = array(
            'operation'             => 'create',
            'notification_key_name' => $notification_group_name,
            'registration_ids'      => $registatoin_ids
        );
        $headers = array(
            "Authorization: key=".$this->API_KEY,
            "Content-Type: application/json",
            "project_id:".$this->SENDER_ID
        );
        return $this->doRequest($url, $fields, $headers, "POST");
    }

    public function getkeygroup($notification_group_name) {
        $url = "https://fcm.googleapis.com/fcm/notification?notification_key_name=".$notification_group_name;
        $fields = array();
        $headers = array(
            "Authorization: key=".$this->API_KEY,
            "Content-Type: application/json",
            "project_id:".$this->SENDER_ID
        );
        return $this->doRequest($url, $fields, $headers, "GET");
    }

    public function addtogroup($registatoin_ids, $notification_group_key, $notification_group_name) {
        $url = 'https://fcm.googleapis.com/fcm/notification';
        $fields = array(
            'operation'             => 'add',
            'notification_key_name' => $notification_group_name,
            'notification_key'      => $notification_group_key,
            'registration_ids'      => $registatoin_ids
        );
        $headers = array(
            "Authorization: key=".$this->API_KEY,
            "Content-Type: application/json",
            "project_id:".$this->SENDER_ID
        );
        return $this->doRequest($url, $fields, $headers, "POST");
    }

    public function removefromgroup($registatoin_ids, $notification_group_key, $notification_group_name) {
        $url = 'https://fcm.googleapis.com/fcm/notification';
        $fields = array(
            'operation'             => 'remove',
            'notification_key_name' => $notification_group_name,
            'notification_key'      => $notification_group_key,
            'registration_ids'      => $registatoin_ids
        );
        $headers = array(
            "Authorization: key=".$this->API_KEY,
            "Content-Type: application/json",
            "project_id:".$this->SENDER_ID
        );
        return $this->doRequest($url, $fields, $headers, "POST");
    }
}