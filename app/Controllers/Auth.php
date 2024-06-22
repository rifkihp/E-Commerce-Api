<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Auth extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        //
    }

    public function signIn() {
        $model = new \App\Models\Member();

        $_email    = $this->request->getPost('email');
        $_password = $this->request->getPost('password');

        $_DATA = [
            'username' => $_email,
            'password' => $_password
        ];

        $validation = \Config\Services::validation();
		if($validation->run($_DATA, 'auth') == FALSE) {
            foreach($validation->getErrors() as $value) {
                $response = [                
                    'success' => false,
                    'message' => $value
                ];
    
                return $this->respond($response, 200);
            }
        }

        $MCrypt = new \App\Libraries\MCrypt();
		$_password = $MCrypt->encrypt($_DATA['password']);  
        $where = '(email="'.$_email.'" OR phone="'.$_email.'" OR username="'.$_email.'") AND password="'.$_password.'"';

        $user  = $model->select('
            id,
            first_name,
            last_name,
            email,
            phone,
            username,
            dropship_name,
            dropship_phone,
            jenis_user,
            password,
            photo,
            aktif,
            admin
        ')->where($where)->get()->getRowArray();

        $code = 200;
        $response = [
            'success' => false,
            'message' => 'User ID dan password tidak sesuai.'
        ];

		if($user) {
            if(($user['email']==$_email || $user['phone']==$_email || $user['username']==$_email) && $user['password']==$_password) {
                if($user['aktif']==0) {
                    $response = [                
                        'success' => false,
                        'message' => 'Akun belum aktif.'
                    ];
                } else if($user['admin']==0) {
                    $response = [                
                        'success' => false,
                        'message' => 'Akun belum aktif.'
                    ];
                } else {   
                    unset($user['password']);
                    $response = [
                        'success' => true,
                        'message' => 'Proses login berhasil.',
                        'user'    => $user
                    ];
                }
            }
		}

        return $this->respond($response, $code);
    }

    public function signOut() {
        $model = new \App\Models\Member();

        $id_user = $this->request->getVar('id_user');
        $data = $model->select('gcm_regid')->where('id', $id_user)->get()->getRowArray();
        $this->unregisterfcm($data['gcm_regid']);

        $update = $model->where('id', $id_user)->set([
            'gcm_regid' => null,
            'gcm_datetime_update' => null
        ])->update();

        if($update) {
            $response = [
                'success' => true,
                'message' => 'Proses signout berhasil.'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Gagal proses signout.'
            ];
        }
        
        return $this->respond($response, 200);
    }

    public function signUp() {
        $model = new \App\Models\Member();

        $_username     = $this->request->getPost('username');
        $_first_name   = $this->request->getPost('first_name');
        $_last_name    = $this->request->getPost('last_name');
        $_email        = $this->request->getPost('email');
        $_nohp         = $this->request->getPost('nohp');
        $_password     = $this->request->getPost('password');
        $_konfirmasi   = $this->request->getPost('konfirmasi');
        $_referal      = $this->request->getPost('referal');
        $_referensi_id = 0;
        
        $_user_picture = 'default.png';

        $_DATA = [
            'username'   => $_username,
            'first_name' => $_first_name,
            'email'      => $_email,
            'nohp'       => $_nohp,
            'password'   => $_password
        ];

        $validation = \Config\Services::validation();
		if($validation->run($_DATA, 'signup') == FALSE) {
            foreach($validation->getErrors() as $value) {
                $response = [                
                    'success' => false,
                    'message' => $value
                ];
    
                return $this->respond($response, 200);
            }
        }

        //CHECK DUPLIKAT USERNAME;
		$builder = $model->select('COUNT(*) TOTAL');
		$builder->where('username', $_username);
		$check = $builder->get()->getRowArray();
		if($check['TOTAL']>0) {
			$response = [
				'success' => false,
				'message' => 'Username sudah terpakai.'
			];

			return $this->respond($response, 200);
		}

        //CHECK DUPLIKAT NO HP;
		$builder = $model->select('COUNT(*) TOTAL');
		$builder->where('phone', $_nohp);
		$check = $builder->get()->getRowArray();
		if($check['TOTAL']>0) {
			$response = [
				'success' => false,
				'message' => 'No. HP sudah terpakai.'
			];

			return $this->respond($response, 200);
		}

		//CHECK DUPLIKAT EMAIL;
		$builder = $model->select('COUNT(*) TOTAL');
		$builder->where('email', $_email);
		$check = $builder->get()->getRowArray();
		if($check['TOTAL']>0) {
			$response = [
				'success' => false,
				'message' => 'Email sudah terpakai.'
			];

			return $this->respond($response, 200);
		}

        //VALIDASI PENULISAN EMAIL
        if(!filter_var($_email, FILTER_VALIDATE_EMAIL)) {
            $response = [
				'success' => false,
				'message' => 'Penulisan email tidak valid.'
			];

			return $this->respond($response, 200);
        }

        //CHECK VALID REFERAL;
        if($_referal!="") {
            $builder = $model->select('id');
            $builder->where('username', $_referal);
            $check = $builder->get()->getRowArray();
            if($check) {
                $_referensi_id = $check['id'];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Username referal tidak valid.'
                ];
    
                return $this->respond($response, 200);
            }
        }

        //VALIDASI PASSWORD
        if(strlen($_password)<6) {
            $response = [
				'success' => false,
				'message' => 'Password minimal 6 karater.'
			];

			return $this->respond($response, 200);
        } else if($_password!=$_konfirmasi) {
            $response = [
				'success' => false,
				'message' => 'Konfirmasi tidak sesuai dengan password.'
			];

			return $this->respond($response, 200);
        }

        //SIGN UP
        $MCrypt = new \App\Libraries\MCrypt();
		$_password = $MCrypt->encrypt($_DATA['password']);

        $date = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
		$_DATA = [
			'username'           => $_username,
			'referensi_id'       => $_referensi_id,
            'first_name'         => $_first_name,
            'last_name'          => $_last_name,
            'phone'              => $_nohp,
            'email'              => $_email,
            'password'           => $_password,         
            'jenis_user'         => 1,
            'photo'              => $_user_picture,
            'tanggal_jam_create' => $date->format('Y-m-d H:i:s'),
            'hash'               => '',
            'aktif'              => 1,
            'admin'              => 1
		];

        //PROSES INSERT DATA
        $insert = $model->insert($_DATA);
        if($insert) {
            $response = [
                'success'      => true,
                'message'      => 'Registrasi berhasil.', 
                'aktivasi_sms' => 0
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Gagal Proses Registrasi.'
            ];
        }
        
        return $this->respond($response, 200);
    }

    private function unregisterfcm($_reg_id) {

        $gcmRegIds = array();
        array_push($gcmRegIds, $_reg_id);

        $FCM = new \App\Libraries\FCM();
        $mdl_groupmsg = new \App\Models\Groupmsg();

        $builder = $mdl_groupmsg->select('id, notif_key, notif_name');
        $data    = $builder->get()->getResultArray();
        foreach($data as $key => $value) {
            if(strlen($value['notif_key'])>0) {
                $result = $FCM->removefromgroup($gcmRegIds, $value['notif_key'], $value['notif_name']);
                if(isset($result['notification_key'])) {
                    //$message = 'done with key '.$result['notification_key'].'!';
                    //echo ("done with key ".$result["notification_key"]."!");
                }
            }
        }
    }

    private function fcm($_reg_id) {
        $gcmRegIds = [];
        array_push($gcmRegIds, $_reg_id);

        $FCM = new \App\Libraries\FCM();
        $mdl_groupmsg = new \App\Models\Groupmsg();

        $builder = $mdl_groupmsg->select('id, notif_key, notif_name');
        $data    = $builder->get()->getResultArray();
        foreach($data as $key => $value) {
            $notif_key = $value["notif_key"];

            $result = $FCM->getkeygroup($value['notif_name']);
            if(!isset($result['notification_key'])) {
                $notif_key = '';
            }

            if(strlen($notif_key)==0) {
                //echo('Create Group ' .$value['notif_name']. '...<br />');
                $result = $FCM->creategroup($gcmRegIds, $value['notif_name']);
                if(isset($result['notification_key'])) {                    
                    //echo('Group Created with key: '.$result['notification_key'].'<br />');
                    $mdl_groupmsg->set(['notif_key' => $result['notification_key']])->where('id', $value['id'])->update();
                } else

                //group already created!
                if(trim($result['error'])=='notification_key already exists') {
                    //echo('Group already Create. Getting key...<br />');
                    $result = $FCM->getkeygroup($value['notif_name']);
                    if(isset($result['notification_key'])) {
                        //echo('key Get: '. $result['notification_key'] .'<br />');
                        $mdl_groupmsg->set(['notif_key' => $result['notification_key']])->where('id', $value['id'])->update();
                    }
                }
            } else {
                //add device to group
                //echo('add device to group...<br />');
                $result = $FCM->addtogroup($gcmRegIds, $value['notif_key'], $value['notif_name']);
                if(isset($result['notification_key'])) {
                    echo ('done with key '.$result['notification_key'].'!');
                    $mdl_groupmsg->set(['notif_key' => $result['notification_key']])->where('id', $value['id'])->update();
                }
            }
        }
    }

    public function fcmRegId() {
        $model     = new \App\Models\Member();
        $mdl_guest = new \App\Models\Guest();
        $date      = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
		
        $_userid = $this->request->getPost('userid');
        $_reg_id = $this->request->getPost('reg_id');

        //AS GUEST
        if($_userid==0) {
            $builder = $mdl_guest->select('id')->where('gcm_regid', $_reg_id);
            $data = $builder->get()->getRowArray();

            if($data) {
                $mdl_guest->set([
                    'gcm_regid'       => $_reg_id,
                    'datetime_update' => $date->format('Y-m-d H:i:s')
                ])
                ->where('id', $data['id'])
                ->update();
            } else {
                $mdl_guest->insert([
                    'gcm_regid'       => $_reg_id,
                    'datetime_update' => $date->format('Y-m-d H:i:s')
                ]);

                $this->fcm($_reg_id);
            }

        //AS USER
        } else {
            //clear gcm_regid guest by reg id
            $mdl_guest->where('gcm_regid', $_reg_id)->delete();

            $builder = $model->select('id')->where(['gcm_regid' => $_reg_id, 'id' => $_userid]);
            $data = $builder->get()->getRowArray();

            if(!$data) {
                $this->fcm($_reg_id);
            }

            $model->set([
                'gcm_regid' => $_reg_id,
                'gcm_datetime_update' => $date->format('Y-m-d H:i:s')
            ])
            ->where('id', $_userid)
            ->update();
        }

        $response = [
            'success' => true,
            'message' => 'Registrasi FCM reg id berhasil.'
        ];

        return $this->respond($response, 200);
    }
}
