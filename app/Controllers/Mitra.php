<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Mitra extends BaseController
{

    use ResponseTrait;

    public function index() {
		$model = new \App\Models\Member();

		//$page     = $this->request->getVar('page');
        //$limit    = 1000;
        //$start    = $limit*($page-1);
		
		/*$query    = $this->request->getVar('query');
		if($query!='') {
			$query = '(A.first_name LIKE \'%'.$query.'%\' OR A.last_name LIKE \'%'.$query.'%\' OR A.email LIKE \'%'.$query.'%\' OR A.username LIKE \'%'.$query.'%\')';
		}*/

		//MITRA
		$builder = $model->db->table('member A');
		$builder->select('
            A.id,
            CONCAT(A.first_name, \' \', A.last_name) nama, 
            COALESCE(B.no_hp, \'\') no_hp, 
            A.email,
            A.username,
            COALESCE(B.alamat, \'\') alamat,
            COALESCE(B.latitude, \'\') latitude,
            COALESCE(B.longitude, \'\') longitude,
            COALESCE(B.id_propinsi, 0) province_id,
            COALESCE(B.nama_propinsi, \'\') province,
            COALESCE(B.id_kota, 0) city_id,
            COALESCE(B.nama_kota,\'\') city_name,
            COALESCE(B.id_kecamatan, 0) subdistrict_id,
            COALESCE(B.nama_kecamatan, \'\') subdistrict_name,
            COALESCE(B.kode_pos, \'\') kode_pos,
            IF(A.referensi_id>0,  CONCAT(C.first_name, \' \', C.last_name), \'\') user_referral,
            
            A.photo,
            A.aktif,
            A.admin 
        ');
		$builder->join('customer_address B', 'B.id_member=A.id AND B.as_default=1', 'LEFT');
        $builder->join('member C', 'C.id=A.referensi_id', 'LEFT');
        $builder->where(['A.tipe' => 1]);
		/*if($query!='') {
			$builder->where($query);
		}*/
		$builder->orderBy('A.id', 'ASC');
		//$builder->limit($limit, $start);
		//$data = $builder->getCompiledSelect();
		$data = $builder->get()->getResultArray();
        //$totalData = 0;
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]["aktif"] = $value["aktif"]==1 && $value["admin"]==1;
            }
            //$totalData = ($limit*($page-1));
            //$page++;
        }
		
		//TOTAL MEMBER
		//$builder->select('COUNT(*) total');
        //$builder->where(['A.tipe' => 1]);
		//if($query!='') {
			//$builder->where($query);
		//}
		//$total = $builder->getCompiledSelect();
		//$total = $builder->get()->getRowArray();
		$total = count($data);

        $response = [
            'totalCount' => $total,
            //'total'      => $totalData+count($data),
            //'totalData'  => count($data),
            //'next_page'  => $page,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);
	}

    public function pilihanDataMitra() {
        $model = new \App\Models\Member();

		$builder = $model->db->table('member A');
		$builder->select('
            A.id,
            CONCAT(A.first_name, \' \', A.last_name) nama, 
            COALESCE(B.no_hp, \'\') no_hp, 
            A.email,
            A.username,
            COALESCE(B.alamat, \'\') alamat,
            COALESCE(B.latitude, \'\') latitude,
            COALESCE(B.longitude, \'\') longitude,
            COALESCE(B.id_propinsi, 0) province_id,
            COALESCE(B.nama_propinsi, \'\') province,
            COALESCE(B.id_kota, 0) city_id,
            COALESCE(B.nama_kota,\'\') city_name,
            COALESCE(B.id_kecamatan, 0) subdistrict_id,
            COALESCE(B.nama_kecamatan, \'\') subdistrict_name,
            COALESCE(B.kode_pos, \'\') kode_pos,
            A.photo,
            A.aktif,
            A.admin 
        ');
		$builder->join('customer_address B', 'B.id_member=A.id AND B.as_default=1', 'LEFT');
        $builder->where(['A.tipe' => 1, 'A.aktif' => 1, 'A.admin' => 1]);
		$builder->orderBy('A.id', 'ASC');
		$data = $builder->get()->getResultArray();
        
        $response = [
            'totalCount' => count($data),
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);
    }
}
