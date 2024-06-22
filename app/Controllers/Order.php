<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Order extends BaseController
{

    use ResponseTrait;

    public function index() {
        $model = new \App\Models\CustomerOrderHeader();

        $id_member = $this->request->getVar('id_member');
        $id_cat    = $this->request->getVar('id_cat');
        
        //0: Menunggu Pembayaran; 1: Pembayaran Ditolak; 2: Pembayaran Diterima; 3: Konfirmasi Pembayaran; 4: Dibatalkan; 5: pesanan diterima; 6: pesanan sedang dikirim
        $filter = '';
        if($id_cat==1) {
            $filter = ' A.status IN (0, 1) ';
        } else if($id_cat==2) {
            $filter = ' A.status IN (2, 7) ';
        } else if($id_cat==3) {
            $filter = ' A.status IN (6, 8) ';
        } else if($id_cat==4) {
            $filter = ' A.status=5 ';
        } else if($id_cat==5) {
            $filter = ' A.status=4 ';
        }
        
        $builder = $model->db->table('customer_order_header A')
        ->join('customer_order_ongkir B', 'A.id=B.id_header', 'LEFT')
        ->join('member C', 'C.id=A.id_mitra', 'LEFT')
        ->select('
            A.id, 
            A.kode no_transaksi, 
            DATE_FORMAT(A.tanggal_jam, \'%d-%m-%Y %H:%i\') tgl_transaksi, 
            A.nama, 
            A.id_mitra,
            CONCAT(C.first_name, \' \', C.last_name) nama_mitra,
            A.pembayaran, 
            A.total_qty qty, 
            A.total jumlah, 
            B.etd estimasi, 
            B.kode_layanan kode_kurir, 
            B.layanan kurir, 
            B.no_resi noresi, 
            A.status
        ')
        ->where(
            'A.id_member='.$id_member . (strlen($filter)>0?' AND '.$filter:'')
        )
        ->orderBy(
            'A.id', 'DESC'
        );
        $data = $builder->get()->getResultArray();

        $response = [
            'totalCount' => count($data),
            'topics'     => $data
        ];

        
		return $this->respond($response, 200);
    }

    public function getDetailOrder() {
        $db = \Config\Database::connect();

        $id_member = $this->request->getVar('id_user');
        $id_order  = $this->request->getVar('id_order');
    
        $sql = 'SELECT * FROM customer_order_header WHERE id='.$id_order;
        $data = $db->query($sql)->getRowArray();
        
        //DATA ALAMAT
        $data_alamat = [
            'id'               => $data['id'],
            'nama'             => $data['nama'],
            'alamat'           => $data['alamat'],
            'latitude'         => $data['latitude'],
            'longitude'        => $data['longitude'],
            'id_propinsi'      => $data['id_propinsi'],
            'nama_propinsi'    => $data['nama_propinsi'],
            'id_kota'          => $data['id_kota'],
            'nama_kota'        => $data['nama_kota'],        
            'id_kecamatan'     => $data['id_kecamatan'],
            'nama_kecamatan'   => $data['nama_kecamatan'],
            'kode_pos'         => $data['kode_pos'],
            'no_hp'            => $data['no_hp'],
            'is_dropship'      => $data['is_dropship']=='Y',
            'dropship_name'    => $data['dropship_name'],
            'dropship_phone'   => $data['dropship_phone'],
            'email_notifikasi' => $data['email_notifikasi']
        ];

        //DATA MITRA
        $sql = 'SELECT 
            A.id,
            CONCAT(A.first_name, \' \', A.last_name) nama, 
            A.phone no_hp, 
            A.email,
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
        FROM
            member A LEFT JOIN 
            customer_address B ON A.id=B.id_member AND B.as_default=1
        WHERE
            A.id='.$data['id_mitra'];
        $data_mitra = $db->query($sql)->getRowArray();

        //DATA VOUCHER
        $sql = 'SELECT
            kode_voucher,
            nominal,
            tipe_voucher,
            jenis_voucher
        FROM 
            customer_order_voucher
        WHERE
            id_header='.$data['id'];
        $data_voucher = $db->query($sql)->getRowArray();
                
        //DATA ONGKIR
        $sql = 'SELECT
            B.id id_kurir, 
            B.kode kode_kurir,
            B.nama nama_kurir,
            A.kode_layanan kode_service,
            A.layanan nama_service,
            A.nominal,
            A.etd,
            A.tarif,
            B.gambar gambar_kurir
        FROM
            customer_order_ongkir A LEFT JOIN 
            ekspedisi B ON A.id_ekspedisi=B.id
        WHERE
            A.id_header='.$data['id'];
        $data_ongkir = $db->query($sql)->getRowArray();
        $data_ongkir['tarif'] = 'Rp '.number_format($data_ongkir['tarif'], 0, '.', ',');

        //DATA PEMBAYARAN
        $sql = 'SELECT
            A.bank_id id, 
            A.no_rekening,
            A.nama_pemilik_rekening,
            A.nama_bank,
            A.cabang,
            C.gambar
        FROM
            customer_order_transfer_bank A LEFT JOIN 
            bank_transfer C ON A.bank_id=C.id
        WHERE
            A.id_header='.$data['id'];
        $data_pembayaran = $db->query($sql)->getRowArray();
       
        //CART
        $sql = 'SELECT 
            A.id, 
            A.id_produk,
            A.kode, 
            A.nama, 
            A.gambar, 
            A.ukuran, 
            A.warna, 
            A.jumlah, 
            A.satuan,
            A.berat, 
            A.harga_beli, 
            A.harga_jual, 
            A.harga_diskon, 
            A.persen_diskon, 
            A.sub_total subtotal, 
            A.grand_total grandtotal
        FROM
            customer_order_detail A
        WHERE
            A.id_header='.$data['id'];
        $detail = $db->query($sql)->getResultArray();

        $cart  = [];
        $total = 0;
        
        foreach($detail as $i => $value) {
            
            $produk = [
                'id'            => $value['id_produk'],
                'kode'          => $value['kode'],
                'nama'          => $value['nama'],
                'penjelasan'    => '',
                'foto_produk'   => $value['gambar'],
                'satuan'        => $value['satuan'],
                'harga_beli'    => $value['harga_beli'],
                'harga_jual'    => $value['harga_jual'],
                'harga_diskon'  => $value['harga_diskon'],
                'persen_diskon' => $value['persen_diskon'],
                'subtotal'      => $value['subtotal'],

                'berat'         => $value['berat'],
                'minimum_pesan' => 0,

                'periode_promo' => '',
                'kode_grup'     => 0,
            ];

            $carstok = [
                'id'       => $value['id_produk'],
                'ukuran'   => $value['ukuran'],
                'warna'    => $value['warna'],
                'qty'      => $value['jumlah'],
                'berat'    => $value['berat'],
                'harga'    => $value['subtotal'],
                'id_mitra' => $data['id_mitra']
            ];
            
            array_push($cart, [
                'produk'   => $produk,
                'cartstok' => $carstok,
                'hari'     => 0,
                'jam'      => 0,
                'menit'    => 0,
                'detik'    => 0,
                'timeover' => ''
            ]);
            
            $total+=$value['grandtotal'];
            
        }

        $response = [
            'success'    => true,
            'message'    => 'Load data berhasil.',

            'alamat'     => $data_alamat,
            'mitra'      => $data_mitra,
            'voucher'    => $data_voucher,
            'ongkir'     => $data_ongkir,
            'pembayaran' => $data_pembayaran,
            'cart'       => $cart
        ];
            
        return $this->respond($response, 200);
    }

    public function cancelOrder() {
        $model = new \App\Models\CustomerOrderHeader();

        $id_member = $this->request->getVar('id_user');
        $id_order  = $this->request->getVar('id_order');

        $data = $model->select('status')->where(['id_member' => $id_member, 'id' => $id_order])->get()->getRowArray();
        
        if($data) {
            $status = $data["status"];
            if($status==0 || $status==1 || $status==3) {
                $date = new \DateTime("now", new \DateTimeZone('Asia/Jakarta'));
                $model->where(['id_member' => $id_member, 'id' => $id_order])->set([
                    'status' => 4, 
                    'date_update_status' => $date->format('Y-m-d H:i:s')])->update();

                $response = [
                    'success' => true, 
                    'message' => 'Proses pembatalan pesanan berhasil.'
                ];
            } else {
                $response = [
                    'success' => false, 
                    'message' => 'Pesanan tidak bisa dibatalkan.'
                ];
            }
        } else {
            $response = [
                'success' => true, 
                'message' => 'Proses pembatalan pesanan berhasil.'
            ];
        }
        
        return $this->respond($response, 200);        
    }
}
