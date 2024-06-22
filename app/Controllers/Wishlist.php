<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Wishlist extends BaseController
{

    use ResponseTrait;

    public function index() {
        
        $model = new \App\Models\Produk();
        $id_user = $this->request->getVar('id_user');

        $builder = $model->db->table('produk_wishlist D');
        $builder->select('
            A.id,
            A.kode,
            A.nama,
            0 id_category,
            \'\' category_name,
            A.penjelasan,
            A.gambar_utama foto_produk,
            A.satuan,
            A.harga_modal harga_beli,
            A.harga_jual,
            A.harga_jual harga_grosir,
            A.harga_diskon,
            A.persen_diskon,
            A.berat,
            \'\' list_ukuran,
            \'\' ukuran,
            \'\' list_warna,
            \'\' warna,
            1 qty,
            1 max_qty,
            A.minimum_pesan,
            0 produk_promo,
            A.produk_featured,
            A.produk_terbaru,
            A.produk_preorder,
            A.produk_soldout,
            A.produk_grosir,
            A.produk_freeongkir,
            A.produk_cod,
            A.rating, 
            A.responden,
            A.review,
            A.status,     
            IF(A.harga_diskon>0 AND IF(A.from_date_harga_diskon IS NOT NULL, A.from_date_harga_diskon<=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1) AND IF(A.to_date_harga_diskon IS NOT NULL, A.to_date_harga_diskon>=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1), 1, 0) harga_promo, 
            IF(A.persen_diskon>0 AND IF(A.from_date_persen_diskon IS NOT NULL, A.from_date_persen_diskon<=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1) AND IF(A.to_date_persen_diskon IS NOT NULL, A.to_date_persen_diskon>=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1), 1, 0) diskon_promo,
            CONCAT(
                IF(A.from_date_harga_diskon IS NOT NULL, DATE_FORMAT(A.from_date_harga_diskon, \'%d-%m-%Y\'), \'\'),
                IF(A.to_date_harga_diskon IS NOT NULL, CONCAT(\' sd \', DATE_FORMAT(A.to_date_harga_diskon, \'%d-%m-%Y\')), \'\')
            ) periode_harga_diskon,
            CONCAT(
                IF(A.from_date_persen_diskon IS NOT NULL, DATE_FORMAT(A.from_date_persen_diskon, \'%d-%m-%Y\'), \'\'),
                IF(A.to_date_persen_diskon IS NOT NULL, CONCAT(\' sd \', DATE_FORMAT(A.to_date_persen_diskon, \'%d-%m-%Y\')), \'\')
            ) periode_persen_diskon,
            
            B.id mitra_id,
            CONCAT(B.first_name, \' \', B.last_name) mitra_nama, 
            
            COALESCE(C.alamat, \'\') alamat,
            COALESCE(C.latitude, \'\') latitude,
            COALESCE(C.longitude, \'\') longitude,
            COALESCE(C.id_propinsi, 0) province_id,
            COALESCE(C.nama_propinsi, \'\') province,
            COALESCE(C.id_kota, 0) city_id,
            COALESCE(C.nama_kota,\'\') city_name,
            COALESCE(C.id_kecamatan, 0) subdistrict_id,
            COALESCE(C.nama_kecamatan, \'\') subdistrict_name,
            COALESCE(C.kode_pos, \'\') kode_pos,
            COALESCE(C.no_hp, \'\') no_hp, 

            B.photo logo, 
            A.kode_grup
        ');
        $builder->join('produk A', 'D.id_produk=A.id', 'LEFT');        
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where('D.id_user', $id_user);
        $builder->orderBy('A.id', 'ASC');
        $data = $builder->get()->getResultArray();
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;
                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = $data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01));
                }
                
                $data[$key]['periode_promo'] = '';
                if($data[$key]['persen_diskon']>0 && strlen(trim($value['periode_persen_diskon']))>0) {
                    $data[$key]['periode_promo'] = $value['periode_persen_diskon'];
                } else if($data[$key]['harga_diskon']>0 && strlen(trim($value['periode_harga_diskon']))>0) {
                    $data[$key]['periode_promo'] = $value['periode_harga_diskon'];
                }
                $data[$key]['mitra'] = [
                    'id'               => $value['mitra_id'],
                    'nama'             => $value['mitra_nama'],
                    'alamat'           => $value['alamat'],
                    'latitude'         => $value['latitude'],
                    'longitude'        => $value['longitude'],
                    'province_id'      => $value['province_id'],
                    'province'         => $value['province'],
                    'city_id'          => $value['city_id'],
                    'city_name'        => $value['city_name'],
                    'subdistrict_id'   => $value['subdistrict_id'],
                    'subdistrict_name' => $value['subdistrict_name'],
                    'kode_pos'         => $value['kode_pos'],
                    'no_hp'            => $value['no_hp'],
                    'logo'             => $value['logo']
                ];

                unset($data[$key]['mitra_id']);
                unset($data[$key]['mitra_nama']);
                unset($data[$key]['alamat']);
                unset($data[$key]['latitude']);
                unset($data[$key]['longitude']);
                unset($data[$key]['province_id']);
                unset($data[$key]['province']);
                unset($data[$key]['city_id']);
                unset($data[$key]['city_name']);
                unset($data[$key]['subdistrict_id']);
                unset($data[$key]['subdistrict_name']);
                unset($data[$key]['kode_pos']);
                unset($data[$key]['no_hp']);
                unset($data[$key]['logo']);
            }
        } else {
            $data = [];
        }

        $response = [
            'totalCount' => count($data),
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);

    }

    public function insert() {
		$model = new \App\Models\Wishlist();
		$_DATA = [
			'id_user'   => $this->request->getPost('id_user'),
			'id_produk' => $this->request->getPost('id_produk')
		];

		//PROSES INSERT WISHLIST
		$model->insert($_DATA);
		$response = [
			'success' => true,
			'message' => 'Tambah Wishlist berhasil.'
		];
		return $this->respond($response, 200);
	}

    public function delete() {
		$model = new \App\Models\Wishlist();
		$_DATA = [
			'id_user'   => $this->request->getPost('id_user'),
			'id_produk' => $this->request->getPost('id_produk')
		];

		//PROSES INSERT WISHLIST
		$model->where($_DATA)->delete();
		$response = [
			'success' => true,
			'message' => 'Hapus Wishlist berhasil.'
		];
		return $this->respond($response, 200);
	}

}
