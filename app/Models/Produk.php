<?php

namespace App\Models;

use CodeIgniter\Model;

class Produk extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'produk';
    protected $primaryKey           = 'id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDeletes       = false;
    protected $protectFields        = true;
    protected $allowedFields        = [ 'id', 'id_member', 'kode', 'nama', 'penjelasan', 'keyword', 'berat', 'satuan', 'harga_modal', 'harga_jual', 'include5persen',
                                        'harga_diskon', 'tipe_diskon','from_date_harga_diskon', 'to_date_harga_diskon', 'persen_diskon', 'from_date_persen_diskon', 'to_date_persen_diskon', 'status', 'minimum_pesan', 'id_brand',
                                        'rating', 'responden', 'review', 'viewer', 'produk_terbaru', 'produk_featured', 'produk_preorder', 'produk_soldout', 'produk_freeongkir', 'produk_cod', 'produk_grosir', 
                                        'tanggal_create', 'tanggal_update', 'user_update', 'img_src', 'id_lokasi', 'gambar_utama', 'kode_grup'];

    // Dates
    protected $useTimestamps        = false;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = [];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];
}
