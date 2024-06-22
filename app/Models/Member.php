<?php

namespace App\Models;

use CodeIgniter\Model;

class Member extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'member';
    protected $primaryKey           = 'id';
    protected $useAutoIncrement     = true;
    protected $insertID             = 0;
    protected $returnType           = 'array';
    protected $useSoftDeletes       = false;
    protected $protectFields        = true;
    protected $allowedFields        = ['id', 'username', 'referensi_id', 'first_name', 'last_name', 'email', 'password', 'phone', 'dropship_name', 'dropship_phone', 'referal',
                                        'jenis_user', 'photo', 'gcm_regid', 'gcm_datetime_update', 'hash', 'aktif', 'admin', 'saldo', 'kode_reset_password', 'is_open', 
                                        'tanggal_jam_create', 'alamat_kirim', 'tipe'];

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
