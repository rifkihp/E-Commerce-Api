<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class KonfirmasiBayar extends BaseController
{

    use ResponseTrait;

    public function index() {
        
        $model = new \App\Models\KonfirmasiBayar();

		$page     = $this->request->getVar('page');
        $limit    = 50;
        $start    = $limit*($page-1);
		
		//DATA
		$builder = $model->db->table('konfirmasi_pembayaran A');
        $builder->join('member B', 'A.id_member=B.id', 'LEFT');

		$builder->select('
            A.id,
            A.kode_pemesanan,
            DATE_FORMAT(A.tanggal_jam, \'%d-%m-%Y %H:%i:%s\') AS tgl_jam,
            A.nama_bank_pengirim AS bank_pengirim,
            A.nama_pemilik_rekening AS nama_pengirim,
            A.jumlah_transfer,
            CONCAT(B.first_name, \' \', B.last_name) nama,
            A.status,
            A.bank_tujuan,
            A.penjelasan,
            A.photo 
        ');
		$builder->orderBy('A.tanggal_jam', 'DESC');
		$builder->limit($limit, $start);
		//$data = $builder->getCompiledSelect();
		$data = $builder->get()->getResultArray();
        $totalData = 0;
        if($data) {
            $totalData = ($limit*($page-1));
            $page++;
        }
		
		//TOTAL DATA
        $builder = $model->db->table('konfirmasi_pembayaran A');
        $builder->join('member B', 'A.id_member=B.id', 'LEFT');

		$builder->select('COUNT(*) total');
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
 
    public function validasiKonfirmasiBayar() {

        $model = new \App\Models\KonfirmasiBayar();
        $date = new \DateTime("now", new \DateTimeZone('Asia/Jakarta'));
		
        $id = ($db->checkIntegerRange($_POST["id"])?$_POST["id"]:"");
        $status = ($db->checkIntegerRange($_POST["status"])?$_POST["status"]:"");
        $tanggal_jam = $date->format('Y-m-d H:i:s');
    
        //get data kode pemesanan
        $data_konfirmasi = $db->getFieldValue("konfirmasi_pembayaran", array("id", "id_member", "kode_pemesanan", "jumlah_transfer"), array("id" => $id));
        
        //get data pemesanan
        $data = $db->getFieldValue("customer_order_header", array("id", "kode", "id_member", "email_notifikasi", "is_dropship"), array("kode" => $data_konfirmasi[0]["kode_pemesanan"]));
        
        //VALIDASI
        if($status==2) {
            if(count($data)==0) {
                $db->mysql_close();
                die(json_encode(array("success" => false, "message" => "Kode pemesanan tidak ditemukan!")));        
            }
            
            $sql = "SELECT COUNT(*) AS TOTAL FROM konfirmasi_pembayaran WHERE id_header='".$data[0]["id"]."' AND status='".$status."'";
            $db->mysql_query($sql, $rec_count, $rs);
            if($rs[0]["TOTAL"]>0) {
                $db->mysql_close();
                die(json_encode(array("success" => false, "message" => "Kode pemesanan sudah dikonfirmasi!")));        
            }
            
            //UPDATE PRODUK VARIAN
            $detail = $db->getFieldValue("customer_order_detail", array("*"), array("id_header" => $data[0]["id"]));
            foreach ($detail as $key => $value) {
                if($data[0]["is_dropship"]=="Y") {
                    $db->mysql_update(
                        "produk_varian", 
                        array(
                            "dropship" => "(dropship+".$value["jumlah"].")", 
                            "keep" => "(keep-".$value["jumlah"].")"), 
                        array(
                            "id_produk" => $value["id_produk"], 
                            "ukuran" => $value["ukuran"],
                            "warna" => $value["warna"]), 
                        array(
                            "dropship", 
                            "keep")
                    );
                } else {
                    $db->mysql_update(
                        "produk_varian", 
                        array(
                            "terjual" => "(terjual+".$value["jumlah"].")", 
                            "keep" => "(keep-".$value["jumlah"].")"), 
                        array(
                            "id_produk" => $value["id_produk"], 
                            "ukuran" => $value["ukuran"],
                            "warna" => $value["warna"]), 
                        array(
                            "terjual", 
                            "keep")
                    );
                }
            }
        }
        
        $db->mysql_update(
            "konfirmasi_pembayaran", 
            array(
                "id_header" => $data[0]["id"],
                "status" => $status,
                "tanggal_jam_update_status" => $tanggal_jam
            ),      
            array("id" => $id)
        );
        
        if(count($data)>0) {
            //INSERT HISTORI PEMESANAN
            $db->mysql_insert("histori_status_pemesanan", array(
                "id" => $db->getLastNumber("histori_status_pemesanan", "id"),
                "id_pemesanan" => $data[0]["id"],
                "tanggal_jam" => $tanggal_jam,
                "penjelasan" => $status==2?"Admin menerima pembayaran customer.":"Admin menolak pembayaran customer."
            ));
            
            $db->mysql_update("customer_order_header", array("status" => $status, "date_update_status" => $date->format('Y-m-d H:i:s'), "is_open" => "N"), array("id" => $data[0]["id"]));
            
            //SEND EMAIL TO USER
            $data_umum = $db->getFieldValue("umum", array("website", "mask_email"));
            $email_from = $data_umum[0]["mask_email"];
            $from_alias = $data_umum[0]["website"];

            $data_member = $db->getFieldValue("member", array("CONCAT(first_name, ' ', last_name) AS nama", "email",  "gcm_regid"), array("id" => $data_konfirmasi[0]["id_member"]));
            $subject = "Validasi Konfirmasi Pembayaran";
            $detail_message = "
                Pembayaran Anda sebesar Rp ".number_format($data_konfirmasi[0]["jumlah_transfer"], 0, '.', ',')." untuk nomer pemesanan <b>#".$data_konfirmasi[0]["kode_pemesanan"]."/b> ".
                ($status==2?"telah Kami terima.":"<b>DITOLAK</b>. Silahkan melakukan pembayaran ulang.").".";

            $email_to = $data[0]["id_member"]=="0"?$data[0]["email_pengirim"]:$data_member[0]["email"];
            $to_alias = $data[0]["id_member"]=="0"?$data[0]["email_pengirim"]:$data_member[0]["nama"];
            $db->sendEmail($email_to, $to_alias, $subject, $detail_message, $email_from, $from_alias);
            
            //PUSH NOTIFICATION:
            include $root."includes/php/GCM.php";    
            $gcm = new GCM();            
            //PUSH NOTIFICATION USER
            $gcmRegIds = array();
            foreach ($data_member as $key => $value) {
                if(strlen($value["gcm_regid"])>0) {
                    array_push($gcmRegIds, $value['gcm_regid']);
                }
            }
            if(count($gcmRegIds)>0) {
                $message = json_encode(
                    array(
                        "success" => true,
                        "from" => "Admin: ".$data_umum[0]["website"],
                        "judul" => $subject,
                        "message" => $db->limitTextKonten($detail_message, 1000),
                        "tanggal_jam" => $tanggal_jam
                    )
                );            
                $result = $gcm->send_notification($gcmRegIds, array("tipe" => "notifikasi", "message" => $message));
                $db->mysql_update(
                    "message", 
                    array(
                        "push_result" => $result           
                    ),
                    array("id" => $id)
                );
                //echo($result);
            }
        }
        
        //$_SESSION["alert_success"] = "Proses validasi menjadi ".($status==2?"valid":"invalid")." berhasil.";
        $db->mysql_close();
        die(json_encode(array("success" => true)));

    }

}
