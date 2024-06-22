<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Informasi extends BaseController
{

    use ResponseTrait;

    private function limitTextKonten($string, $limit = 500) {

        $string = strip_tags($string);

        if (strlen($string) > $limit) {



            // truncate string

            $stringCut = substr($string, 0, $limit);



            // make sure it ends in a word so assassinate doesn't become ass...

            $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 

        }

        

        return $string;

    }

    public function index() {

        $model = new \App\Models\Informasi();
        $page  = $this->request->getVar('page');
        $limit = 20;
        $start = $limit*($page-1);

        $data = [];
        if($page==1) {
            $id_user = $this->request->getVar('id_user');

            //GET DATA USER
            $model_member = new \App\Models\Member();
            $user = $model_member->where('id', $id_user)->get()->getRowArray();
            
            if($user) {
                //GET SERTIFIKAT
                $model_sertifikat = new \App\Models\Sertifikat();
                $result = $model_sertifikat->select('
                    id, 
                    DATE_FORMAT(date_create, \'%d-%m-%Y\') tanggal, 
                    \'Sertifikat Event\' judul, 
                    CONCAT(\'Download sertifikat Anda di: <br /><br /><a href="https://galeripepi.com/sertifikat/server/public/uploads/sertifikat/\', docfile, \'">Download Sertifikat Saya</a>\') konten, 
                    \'39681427.png\' gambar
                ')->where('email', $user['email'])->get()->getResultArray();

                
                if($result) {
                    foreach($result as $key => $value) {
                        $result[$key]['header']  = "Silahkan download sertifikat Event Galeri PEPI disini."; //preg_replace( "/\r|\n/", "", $this->limitTextKonten($value['konten'], 150));
                        array_push($data, $result[$key]);
                    }
                }   
            }
        }
        
        
        $builder = $model->select('
            id, 
            DATE_FORMAT(datetime_update, \'%d-%m-%Y\') tanggal, 
            judul, 
            konten, 
            gambar
        ');

        $builder->orderBy('datetime_update', 'DESC');
        $builder->limit($limit, $start);
		$result = $builder->get()->getResultArray();
        if($result) {
            foreach($result as $key => $value) {
                $result[$key]['header']  = preg_replace( "/\r|\n/", "", $this->limitTextKonten($value['konten'], 150));
                array_push($data, $result[$key]);
            }
        }

         //GET TOTAL
		$builder = $model->select('COUNT(*) total');
		$total   = $builder->get()->getRowArray();
		$total   = $total['total'];

        $response = [
            'totalCount' => $total,
            'next_page'  => count($data)==0?$page:$page+1,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);
    }
}
