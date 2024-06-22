<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Home extends BaseController
{

    use ResponseTrait;

    public function index()
    {
        return view('welcome_message');
    }

    public function home() {

        $iduser = $this->request->getVar('id_user');
        $model_banner = new \App\Models\Banner();

        $builder = $model_banner->select('
            id, 
            nama, 
            id_kategori kategori, 
            filename AS banner
        ')->where('is_aktif', 'Y');
        $banner = $builder->get()->getResultArray();

        $model_shortcut = new \App\Models\Shortcut();
        $builder = $model_shortcut->select('
            id, 
            nama, 
            direction, 
            icon
        ');
        $shortcut = $builder->get()->getResultArray();

        $model_category = new \App\Models\Category();
        $builder = $model_category->select('
            id, 
            nama, 
            penjelasan, 
            header
        ')->where(['id_parent' => 0, 'kode_grup' => 1]);
        $category = $builder->get()->getResultArray();
        
        
        $builder = $model_category->select('
            id, 
            nama, 
            penjelasan, 
            header
        ')
        ->where(['id_parent' => 0, 'kode_grup' => 2])
        ->limit(8, 0)
        ->orderBy('A.id', 'RANDOM');
        $category_umkm = $builder->get()->getResultArray();
        
        $model_tabkategori = new \App\Models\TabKategori();
        $builder = $model_tabkategori->select('
            id, 
            nama
        ')->where('is_aktif', 1)->orderBy('id');
        $tabkategori = $builder->get()->getResultArray();
        foreach($tabkategori as $i => $value) {
            $tabkategori[$i]['produk'] = [];
            $tabkategori[$i]['next_page'] = 0;
        }

        $tabindukkategori = [
            [
                'id' => 0,
                'nama' => 'HOME',
                'produk' => [],
                'next_page' => 0
            ]
        ];
        foreach($category as $i => $value) {
            array_push($tabindukkategori, [
                'id' => $value['id'],
                'nama' => $value['nama'],
                'produk' => [],
                'next_page' => 0
            ]);
        }

        $model_umum = new \App\Models\Umum();
        $builder = $model_umum->select('
            tampilkan_shortcut, 
            tampilkan_kategori, 
            tampilkan_induk_kategori, 
            tampilkan_shortcut_bawah
        ');
        $umum = $builder->get()->getRowArray();

        $response = [
            'total_informasi' => 0,
            'total_daftar_pesanan' => 0,
            'banner' => $banner,
            'shortcut' => $shortcut,
            'kategori' => $category, 
            'kategori_umkm' => $category_umkm, 
            'tab_kategori' => $tabkategori, 
            'tab_induk_kategori' => $tabindukkategori, 
            'pengaturan' => $umum
        ];
            
		return $this->respond($response, 200);

    }

}
