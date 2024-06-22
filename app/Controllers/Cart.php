<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Cart extends BaseController
{

    use ResponseTrait;

    public function index()
    {
        //
    }

    public function getCartHeaderDataStore() {
        $db = \Config\Database::connect();

        $id_member = $this->request->getVar('id_member');

        //data umum
        $sql = 'SELECT hari, jam, menit FROM umum WHERE id=1';        
        $data_umum = $db->query($sql)->getRowArray();

        //eliminate data cart
        $sql = 'SELECT
                A.id,
                A.id_produk, 
                A.ukuran, 
                A.warna, 
                A.qty
            FROM 
                (SELECT 
                    id, 
                    id_produk, 
                    ukuran, 
                    warna, 
                    qty, 
                    timeorder, 
                    DATE_ADD(DATE_ADD(DATE_ADD(timeorder, INTERVAL '.$data_umum['hari'].' DAY), INTERVAL '.$data_umum['jam'].' HOUR), INTERVAL '.$data_umum['menit'].' MINUTE) AS timeover, 
                    DATE_FORMAT(NOW(), \'%Y-%m-%d %H:%i:%s\') AS timecurrent 
                FROM 
                    produk_keep 
                WHERE 
                    id_member='.$id_member.' AND
                    locked=0) AS A 
            WHERE 
                A.timeover<=A.timecurrent';
        $data = $db->query($sql)->getResultArray();

        foreach($data as $value) {

            //balikan qty
            $sql = 'UPDATE 
                produk_varian 
            SET 
                jumlah=jumlah+'.$value['qty'].', 
                keep=keep-'.$value['qty'].' 
            WHERE
                id_produk=\''.$value['id_produk'].'\' AND
                ukuran=\''.$value['ukuran'].'\' AND
                warna=\''.$value['warna'].'\'';
            $db->query($sql);

            //delete produk keep
            $sql = 'DELETE FROM produk_keep WHERE id=\''.$value['id'].'\'';
            $db->query($sql);
        }

        $sql = 'SELECT 
            id_mitra id, 
            SUM(berat) total_berat,
            SUM(qty) total_qty,
            SUM(qty*harga) total_jumlah
        FROM 
            produk_keep 
        WHERE 
            id_member='.$id_member.' 
        GROUP BY
           id_mitra';
        $data = $db->query($sql)->getResultArray();
        foreach($data as $i => $value) {
            $sql = 'SELECT
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
            FROM 
                member A LEFT JOIN
                customer_address B ON B.id_member=A.id AND B.as_default=1
            WHERE
                A.id='.$value['id'].'';
            $data[$i]['mitra'] = $db->query($sql)->getRowArray();           
        }
        
        $response = [
            'success' => true,
            'market'  => $data,
            'message' => 'Load data berhasil.'
        ];
            
        return $this->respond($response, 200);
    }

    public function getCartDataStore() {
        $db = \Config\Database::connect();

        $id_member    = $this->request->getVar('id_member');
        $id_mitra     = $this->request->getVar('id_mitra');
    
        //data umum
        $sql = 'SELECT hari, jam, menit FROM umum WHERE id=1';        
        $data_umum = $db->query($sql)->getRowArray();

        //eliminate data cart
        $sql = 'SELECT
                A.id,
                A.id_produk, 
                A.ukuran, 
                A.warna, 
                A.qty
            FROM 
                (SELECT 
                    id, 
                    id_produk, 
                    ukuran, 
                    warna, 
                    qty, 
                    timeorder, 
                    DATE_ADD(DATE_ADD(DATE_ADD(timeorder, INTERVAL '.$data_umum['hari'].' DAY), INTERVAL '.$data_umum['jam'].' HOUR), INTERVAL '.$data_umum['menit'].' MINUTE) AS timeover, 
                    DATE_FORMAT(NOW(), \'%Y-%m-%d %H:%i:%s\') AS timecurrent 
                FROM 
                    produk_keep 
                WHERE 
                    id_member='.$id_member.' AND
                    id_mitra='.$id_mitra.' AND 
                    locked=0) AS A 
            WHERE 
                A.timeover<=A.timecurrent';
        $data = $db->query($sql)->getResultArray();

        foreach($data as $value) {

            //balikan qty
            $sql = 'UPDATE 
                produk_varian 
            SET 
                jumlah=jumlah+'.$value['qty'].', 
                keep=keep-'.$value['qty'].' 
            WHERE
                id_produk=\''.$value['id_produk'].'\' AND
                ukuran=\''.$value['ukuran'].'\' AND
                warna=\''.$value['warna'].'\'';
            $db->query($sql);

            //delete produk keep
            $sql = 'DELETE FROM produk_keep WHERE id=\''.$value['id'].'\'';
            $db->query($sql);
        }

        $sql = 'SELECT 
            D.id,
            D.id_produk, 
            D.ukuran, 
            D.warna, 
            D.qty, 
            TIMESTAMPDIFF(DAY, D.timecurrent, D.timeover) AS hari, 
            MOD(TIMESTAMPDIFF(HOUR, D.timecurrent, D.timeover), 24) AS jam, 
            MOD(TIMESTAMPDIFF(MINUTE, D.timecurrent, D.timeover), 60) AS menit, 
            MOD(TIMESTAMPDIFF(SECOND, D.timecurrent, D.timeover), 60) AS detik,
            D.timeover,

                
                A.id_member,
                A.kode,
                A.nama,
                A.penjelasan,
                A.gambar_utama foto_produk,
                A.satuan,

                A.harga_modal harga_beli,
                A.harga_jual,
                A.harga_diskon,
                A.persen_diskon,
                A.include5persen,

                A.berat,
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
                
                A.kode_grup

        FROM 
            (SELECT 
                id,
                id_produk, 
                ukuran, 
                warna,
                berat, 
                qty,
                harga, 
                timeorder, 
                DATE_ADD(DATE_ADD(DATE_ADD(timeorder, INTERVAL '.$data_umum['hari'].' DAY), INTERVAL '.$data_umum['jam'].' HOUR), INTERVAL '.$data_umum['menit'].' MINUTE) AS timeover, 
                DATE_FORMAT(NOW(), \'%Y-%m-%d %H:%i:%s\') AS timecurrent 
            FROM 
                produk_keep 
            WHERE 
                id_member='.$id_member.' AND
                id_mitra='.$id_mitra.' AND 
                locked=0) AS D LEFT JOIN
            produk A ON A.id = D.id_produk
        WHERE 
            D.timeover>D.timecurrent';

        $cart         = [];
        $total_berat  = 0;
        $total_qty    = 0;
        $total_jumlah = 0;        
        $produk_keep = $db->query($sql)->getResultArray();
        foreach($produk_keep as $i => $value) {
            $id_produk = $value['id_produk'];
            $qty       = $value['qty'];

            $qty_komulatif = $qty;
            foreach($produk_keep as $i_ => $value_) {
                if($i!=$i_ && $value['id_produk']==$value_['id_produk']) {
                    $qty_komulatif+=$value_['qty'];
                }
            }
        
            $sql = 'SELECT * FROM produk_grosir WHERE id_produk=\''.$id_produk.'\' AND jumlah_min<=\''.$qty_komulatif.'\' ORDER BY jumlah_min DESC LIMIT 0, 1';
            $data_grosir = $db->query($sql)->getRowArray();

            $value['persen_diskon'] = $value['diskon_promo']==1?$value['persen_diskon']:0;
            $value['harga_diskon']  = $value['harga_promo']==1?$value['harga_diskon']:0;
            $value['harga_jual']    = $data_grosir?$data_grosir['harga']:($value['harga_diskon']>0?$value['harga_diskon']:$value['harga_jual']);
            $value['subtotal']      = $value['harga_diskon']>0?$value['harga_diskon']:$value['harga_jual'];
            if($value['persen_diskon']>0) {                    
                $value['subtotal'] = $value['subtotal']-($value['persen_diskon']*($value['subtotal']*0.01));
            }
                
            $value['periode_promo'] = '';
            if($value['persen_diskon']>0 && strlen(trim($value['periode_persen_diskon']))>0) {
                $value['periode_promo'] = $value['periode_persen_diskon'];
            } else if($value['harga_diskon']>0 && strlen(trim($value['periode_harga_diskon']))>0) {
                $value['periode_promo'] = $value['periode_harga_diskon'];
            }

            $produk = [
                'id'            => $value['id_produk'],
                'kode'          => $value['kode'],
                'nama'          => $value['nama'],
                'penjelasan'    => $value['penjelasan'],
                'foto_produk'   => $value['foto_produk'],
                'satuan'        => $value['satuan'],

                'harga_beli'    => $value['harga_beli'],
                'harga_jual'    => $value['harga_jual'],
                'harga_diskon'  => $value['harga_diskon'],
                'persen_diskon' => $value['persen_diskon'],
                'subtotal'      => $value['subtotal'],

                'berat'         => $value['berat'],
                'minimum_pesan' => $value['minimum_pesan'],

                'periode_promo' => $value['periode_promo'],
                'kode_grup'     => $value['kode_grup'],
            ];

            $carstok = [
                'id'       => $value['id_produk'],
                'ukuran'   => $value['ukuran'],
                'warna'    => $value['warna'],
                'qty'      => $value['qty'],
                'berat'    => $value['berat'],
                'harga'    => $value['subtotal'],
                'id_mitra' => $value['id_member']
            ];
            
            array_push($cart, [
                'produk' => $produk,
                'cartstok' => $carstok,
                'hari'    => $data_umum['hari'],
                'jam'    => $data_umum['jam'],
                'menit'    => $data_umum['menit'],
                'detik'    => 0,
                'timeover' => $value['timeover']
            ]);
                
            $total_berat+=$value['berat'];
            $total_qty+=$value['qty'];
            $total_jumlah+=($value['qty']*$value['subtotal']);
            
        }

        //DATA ALAMAT
        $sql = 'SELECT A.*, COALESCE(B.dropship_name, CONCAT(B.first_name, \' \', B.last_name)) dropship_name, COALESCE(B.dropship_phone, B.phone) dropship_phone FROM customer_address A LEFT JOIN member B ON B.id=A.id_member WHERE A.id_member=\''.$id_member.'\' AND A.as_default=1';
        $data_alamat = $db->query($sql)->getRowArray();
        if($data_alamat) {
            $data_alamat['is_dropship'] = false;
        }
        $response = [
            'success'      => true,
            'cart'         => $cart,
            'data_alamat'  => $data_alamat,
            'total_berat'  => $total_berat,
            'total_qty'    => $total_qty,
            'total_jumlah' => $total_jumlah,
            'message' => 'Load data berhasil.'
        ];
            
        return $this->respond($response, 200);
    }


    public function deleteFromCart() {
        $db = \Config\Database::connect();

        $id_member    = $this->request->getPost('id_member');
        $items        = $this->request->getPost('items');

        $row = explode(';', trim($items));
        foreach($row as $i => $value) {    
            
            $col       = explode(',', $value);  
            $id_produk = $col[0];  
            $ukuran    = $col[1];
            $warna     = $col[2];
            $qty       = $col[3];
            $id_mitra  = $col[4];
    
            $sql = 'SELECT 
                A.id, 
                A.id_produk, 
                A.ukuran, 
                A.warna, 
                A.qty
            FROM 
                produk_keep AS A 
            WHERE 
                A.locked=0 AND 
                A.id_member=\''.$id_member.'\' AND 
                A.id_mitra=\''.$id_mitra.'\' AND
                A.id_produk=\''.$id_produk.'\' AND
                A.ukuran=\''.$ukuran.'\' AND 
                A.warna=\''.$warna.'\'';
            $value = $db->query($sql)->getRowArray();

            //balikan qty
            $sql = 'UPDATE 
                produk_varian 
            SET 
                jumlah=jumlah+'.$value['qty'].', 
                keep=keep-'.$value['qty'].' 
            WHERE
                id_produk=\''.$value['id_produk'].'\' AND
                ukuran=\''.$value['ukuran'].'\' AND
                warna=\''.$value['warna'].'\'';
            $db->query($sql);

            //delete produk keep
            $sql = 'DELETE FROM produk_keep WHERE id=\''.$value['id'].'\'';
            $db->query($sql);
        }

        $response = [
            'success' => true,
            'message' => 'Proses hapus item berhasil.'
        ];
            
        return $this->respond($response, 200);

    }
}
