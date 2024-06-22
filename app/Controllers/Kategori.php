<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Kategori extends BaseController
{
    use ResponseTrait;
    
    public function index()
    {
        $model = new \App\Models\Category();
        $kode_grup = $this->request->getVar('kode_grup');

        $builder = $model->select('
            id, 
            nama, 
            penjelasan, 
            header
        ')->where(['id_parent' => 0, 'kode_grup' => $kode_grup]);
        $data = $builder->get()->getResultArray();

        $response = [
            'totalCount' => count($data),
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);
    }
}
