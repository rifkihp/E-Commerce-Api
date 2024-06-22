<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Produk extends BaseController
{

    use ResponseTrait;

    public function detail($id, $judul) {
        $model = new \App\Models\Produk();

        $builder = $model->db->table('produk A');
        $builder->select('
            A.id,
            A.kode,
            A.nama,
            0 id_category,
            \'&nbsp;\' category_name,
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.id' => $id]);
		$data = $builder->get()->getResultArray();
        $result = [];
        $gambar = [];
        $category = [];
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;
                
                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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

                $data[$key]['header'] = $data[$key]['header'] = preg_replace( "/\r|\n/", "", $this->limitTextKonten($value['penjelasan'], 155));
                $result = $data[$key];
            }

            $mdl_gambar = new \App\Models\ProdukGambar();
            $builder = $mdl_gambar->select('nama_file, as_default')->where('id_produk', $id);
            $gambar = $builder->get()->getResultArray();

            $builder = $model->db->table('produk_to_category A');
            $builder->select('
                B.id,
                B.nama,
                B.penjelasan,
                B.header
            ')->join('category B', 'B.id=A.id_kategori', 'LEFT')->where('A.id_produk', $id);
            $category = $builder->get()->getResultArray();
        }
        
        return view('product_detail', ['data' => $result, 'gambar' => $gambar, 'category' => $category]);
    }

    public function topProdukUmkmDataStore() {
        $model = new \App\Models\Produk();

        $limit     = 10;
        $start     = 0;

        $builder = $model->db->table('produk A');
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.kode_grup' => 2, 'A.status' => 1]);
        $builder->orderBy('A.id', 'RANDOM');
        $builder->limit($limit, $start);
		$data = $builder->get()->getResultArray();
        if($data) {
            foreach($data as $key => $value) {                
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;

                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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
        }

        $response = [
            'totalCount' => count($data),
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);

    }

    public function produkDataStore() {
        $model = new \App\Models\Produk();

        $id_user   = $this->request->getVar('id_user');
        $page      = $this->request->getVar('page');
        $limit     = 20;
        $start     = $limit*($page-1);

        //GET DATA USER
        $model_member = new \App\Models\Member();
        $user = $model_member->where('id', $id_user)->get()->getRowArray();

        $query    = $this->request->getVar('query');
		if($query!='') {
			$query = '(A.kode LIKE \'%'.$query.'%\' OR A.nama LIKE \'%'.$query.'%\' OR A.keyword LIKE \'%'.$query.'%\')';
		}

        $builder = $model->db->table('produk A');
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        //$builder->where(['A.status' => 1]);
        if($query!='') {
			$builder->where($query);
		}
        $builder->orderBy('A.id', 'ASC');
        $builder->limit($limit, $start);
		$data = $builder->get()->getResultArray();
        $totalData = 0;
        if($data) {
            foreach($data as $key => $value) {                
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;

                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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

            $totalData = ($limit*($page-1));
            $page++;
        }

        $response = [
            'total'      => $totalData+count($data),
            'totalData'  => count($data),
            'next_page'  => $page,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);

    }

    public function produkDataStoreUmkm() {
        $model = new \App\Models\Produk();

        $id_user   = $this->request->getVar('id_user');
        $page      = $this->request->getVar('page');
        $limit     = 20;
        $start     = $limit*($page-1);

        //GET DATA USER
        $model_member = new \App\Models\Member();
        $user = $model_member->where('id', $id_user)->get()->getRowArray();

        $query    = $this->request->getVar('query');
		if($query!='') {
			$query = '(A.kode LIKE \'%'.$query.'%\' OR A.nama LIKE \'%'.$query.'%\' OR A.keyword LIKE \'%'.$query.'%\')';
		}

        $builder = $model->db->table('produk A');
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.id_member' => $id_user]);
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
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;

                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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

            $totalData = ($limit*($page-1));
            $page++;
        }

        $response = [
            'total'      => $totalData+count($data),
            'totalData'  => count($data),
            'next_page'  => $page,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);

    }

    public function produkKategoriDataStore() {
        $model = new \App\Models\Produk();

        $id        = $this->request->getVar('id');
        $page      = $this->request->getVar('page');
        $limit     = 20;
        $start     = $limit*($page-1);
        $query     ='';

        if($id==1) {
            $query = 'A.produk_terbaru=1';
        } else
        if($id==2) {
            $query = 'IF((A.harga_diskon>0 AND IF(A.from_date_harga_diskon IS NOT NULL, A.from_date_harga_diskon<=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1) AND IF(A.to_date_harga_diskon IS NOT NULL, A.to_date_harga_diskon>=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1)) OR (A.persen_diskon>0 AND IF(A.from_date_persen_diskon IS NOT NULL, A.from_date_persen_diskon<=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1) AND IF(A.to_date_persen_diskon IS NOT NULL, A.to_date_persen_diskon>=DATE_FORMAT(NOW(), \'%Y-%m-%d\'), 1)), 1, 0)=1';
        } else
        if($id==3) {
            $query = 'B.qty>0 AND B.qty IS NOT NULL';
        }
        
        $builder = $model->db->table('produk A'.($id==3?' LEFT JOIN (SELECT id_produk, SUM(jumlah) AS qty FROM customer_order_detail GROUP BY id_produk) AS B ON A.id=B.id_produk':''));
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.status' => 1]);
        if($query!='') {
			$builder->where($query);
		}
        $builder->orderBy('A.id', 'RANDOM');
        $builder->limit($limit, $start);
        $data = $builder->get()->getResultArray();
        $totalData = 0;
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;

                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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

            $totalData = ($limit*($page-1));
            $page++;
        }

        $response = [
            'total'      => $totalData+count($data),
            'totalData'  => count($data),
            'next_page'  => $page,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);

    }

    public function produkIndukKategoriDataStore() {
        $model = new \App\Models\Produk();

        $id        = $this->request->getVar('id');
        $page      = $this->request->getVar('page');
        $limit     = 20;
        $start     = $limit*($page-1);
		
        $builder = $model->db->table('
            produk A LEFT JOIN 
            (SELECT A1.*, B1.id_parent FROM produk_to_category AS A1 LEFT JOIN category AS B1 ON A1.id_kategori=B1.id WHERE A1.id_kategori='.$id.' OR B1.id_parent='.$id.') AS AA ON A.id=AA.id_produk');
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.status' => 1]);
        $builder->where('AA.id_produk IS NOT NULL');
		
        $builder->orderBy('A.id', 'ASC');
        $builder->limit($limit, $start);
		$data = $builder->get()->getResultArray();
        $totalData = 0;
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;

                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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

            $totalData = ($limit*($page-1));
            $page++;
        }

        $response = [
            'total'      => $totalData+count($data),
            'totalData'  => count($data),
            'next_page'  => $page,
            'topics'     => $data
        ];
            
		return $this->respond($response, 200);

    }

    private function produkDetail($model, $produk_id) {
        
        $builder = $model->db->table('produk A');
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
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.id' => $produk_id]);
		$data = $builder->get()->getResultArray();
        $result = [];
        if($data) {
            foreach($data as $key => $value) {                
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;

                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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

            $result = $data[0];
        }

		return $result;
    }

    public function produkTerkaitDataStore() {
        $model = new \App\Models\Produk();

        $id     = $this->request->getVar('id_produk');
        $limit  = 10;
        $start  = 0;
		
        //IMAGES LIST
        $builder = $model->db->table('gambar_produk')->select('
            id,
            nama_file gambar
        ')->where('id_produk', $id)->orderBy('urutan', 'ASC');
        $list_gambar = $builder->get()->getResultArray();

        //PRODUK LIST
        $builder = $model->db->table('produk_to_category D');
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
        $builder->join('produk A', 'A.id=D.id_produk', 'LEFT');
        $builder->join('member B', 'B.id=A.id_member', 'LEFT');
        $builder->join('customer_address C', 'C.id_member=A.id_member AND as_default=1', 'LEFT');
        $builder->where(['A.status' => 1]);

        $builder->where('D.id_produk<>'.$id.' AND D.id_kategori IN (SELECT id_kategori FROM produk_to_category WHERE id_produk='.$id.')');
		
        $builder->orderBy('A.id', 'RANDOM');
        $builder->limit($limit, $start);
		$data = $builder->get()->getResultArray();
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]['nama']          = trim(ucwords(strtolower($value['nama'])));
                $data[$key]['publish']       = $value['status']==1;
                
                $data[$key]['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
                $data[$key]['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
                $data[$key]['subtotal']      = $data[$key]['harga_diskon']>0?$data[$key]['harga_diskon']:$data[$key]['harga_jual'];
                if($data[$key]['persen_diskon']>0) {                    
                    $data[$key]['subtotal'] = ceil($data[$key]['subtotal']-($data[$key]['persen_diskon']*($data[$key]['subtotal']*0.01)));
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
        }

        //LIST STOK
        $builder = $model->db->table('produk_varian')->select('
            id, ukuran, warna, jumlah'
        )->where('id_produk', $id)->where('jumlah>0');
        $list_stok = $builder->get()->getResultArray();
        
        //PENGATURAN STOK
        $builder = $model->db->table('pengaturan')->select('is_tampilkan_stok, status_stok, parameter_status')->where('id', 1);
        $data_pengaturan = $builder->get()->getRowArray();

        //LIST GROSIR
        $builder = $model->db->table('produk_grosir')->select('id, jumlah_min, jumlah_max, harga')->where('id_produk', $id);
        $list_grosir = $builder->get()->getResultArray();
        
        //UPDATE VIEWER PRODUK
        $builder = $model->select('viewer')->where('id', $id);
        $data_view = $builder->get()->getRowArray();
        $model->where(['id' => $id])->set([
			'viewer' => $data_view['viewer']+1
		])->update();

        $response = [
            'success'          => true,
            'produk'           => $this->produkDetail($model, $id),
            'status_stok'      => $data_pengaturan['status_stok'],
            'parameter_status' => $data_pengaturan['parameter_status'],
            'tampilkan_stok'   => $data_pengaturan['is_tampilkan_stok']=='Y',

            'list_gambar'      => $list_gambar,
            'list_produk'      => $data, 
            'list_stok'        => $list_stok, 
            'list_grosir'      => $list_grosir
        ];
            
		return $this->respond($response, 200);

    }
    
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
}
