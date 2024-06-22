<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Pushnotif extends BaseController
{
    use ResponseTrait;

    public function index() {
        $model = $model = new \App\Models\Pushnotif();
        $builder = $model->select('id, gcm_regid, tipe, notif_id, message, tipe_fcm, respon')->where('respon IS NULL');
        $data = $builder->get()->getResultArray();
        $results = [];
        if($data) {
            $FCM = new \App\Libraries\FCM();           
            foreach($data as $key => $value) {
                $gcmRegIds = [];
                array_push($gcmRegIds, $value['gcm_regid']);
                $_DATA = [
                    'tipe'     => $value['tipe'], 
                    'notif_id' => $value['notif_id'], 
                    'message'  => $value['message']
                ];

                $result = null;
                if($value['tipe_fcm']==1) {
                    $result = json_encode($FCM->sendgroupmsg($value['gcm_regid'], $_DATA));
                } else {
                    $result = json_encode($FCM->sendmsg($gcmRegIds, $_DATA));
                }

                $model->set(['respon' => $result])->where('id', $value['id'])->update();
                
                $response = [
                    'id'      => $value['id'],
                    'success' => true,
                    'result'  => $result
                ];
                array_push($results, $response);
            }
        }

        $response = [
            'totalCount' => count($results),
            'topics'     => $results
        ];

        return $this->respond($response, 200);
    }
}
