<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Member extends BaseController
{

    use ResponseTrait;

    public function index() {
		$model = new \App\Models\Member();

		$page     = $this->request->getVar('page');
        $limit    = 50;
        $start    = $limit*($page-1);
		
		$query    = $this->request->getVar('query');
		if($query!='') {
			$query = '(A.first_name LIKE \'%'.$query.'%\' OR A.last_name LIKE \'%'.$query.'%\' OR A.email LIKE \'%'.$query.'%\' OR A.username LIKE \'%'.$query.'%\')';
		}

		//MEMBER
		$builder = $model->db->table('member A');
		$builder->select('
            A.id,
            CONCAT(A.first_name, \' \', A.last_name) nama, 
            B.no_hp, 
            A.email,
            A.username,
            B.alamat,
            B.latitude,
            B.longitude,
            B.id_propinsi province_id,
            B.nama_propinsi province,
            B.id_kota city_id,
            B.nama_kota city_name,
            B.id_kecamatan subdistrict_id,
            B.nama_kecamatan subdistrict_name,
            B.kode_pos,
            A.photo,
            A.aktif,
            A.admin 
        ');
		$builder->join('customer_address B', 'B.id_member=A.id AND B.as_default=1', 'LEFT');
        $builder->where(['A.tipe' => 0]);
		if($query!='') {
			$builder->where($query);
		}
		$builder->orderBy('A.id', 'ASC');
		$builder->limit($limit, $start);
		//$data = $builder->getCompiledSelect();
		$data = $builder->get()->getResultArray();
        $totalData = 0;
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]["aktif"] = $value["aktif"]==1 && $value["admin"]==1;
            }
            $totalData = ($limit*($page-1));
            $page++;
        }
		
		//TOTAL MEMBER
		$builder->select('COUNT(*) total');
        $builder->where(['A.tipe' => 0]);
		if($query!='') {
			$builder->where($query);
		}
		//$total = $builder->getCompiledSelect();
		$total = $builder->get()->getRowArray();
		$total = $total['total'];

        $response = [
            'totalCount' => $total,
            'total'      => $totalData+count($data),
            'totalData'  => count($data),
            'next_page'  => $page,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);
	}
}
