<?php

namespace App\Models;

use CodeIgniter\Model;

class KonfirmasiBayar extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'konfirmasi_pembayaran';
    protected $primaryKey           = 'id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDeletes       = false;
    protected $protectFields        = true;
    protected $allowedFields        = ['id', 'tanggal_jam', 'kode_pemesanan', 'bank_tujuan', 'jumlah_transfer', 'nama_bank_pengirim', 'nama_pemilik_rekening', 'penjelasan', 'photo', 'id_member', 'id_guest', 'id_header', 'status', 'tanggal_jam_update_status'];

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
