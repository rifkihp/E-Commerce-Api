<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Youtube extends BaseController
{

    use ResponseTrait;

    public function dateDiff($time1, $time2, $precision = 6) {

        //die($time1."----".$time2);
        // If not numeric then convert texts to unix timestamps
        if (!is_int($time1)) {
            $time1 = strtotime($time1);
        }
        if (!is_int($time2)) {
            $time2 = strtotime($time2);
        }

        // If time1 is bigger than time2
        // Then swap time1 and time2
        if ($time1 > $time2) {
            $ttime = $time1;
            $time1 = $time2;
            $time2 = $ttime;
        }

        // Set up intervals and diffs arrays
        $intervals_indo = array('tahun','bulan','hari','jam','menit','detik');
        $intervals = array('year','month','day','hour','minute','second');
        $diffs = array();

        // Loop thru all intervals
        foreach ($intervals as $interval) {
            // Create temp time from time1 and interval
            $ttime = strtotime('+1 ' . $interval, $time1);
            // Set initial values
            $add = 1;
            $looped = 0;
            // Loop until temp time is smaller than time2
            while ($time2 >= $ttime) {
                // Create new temp time from time1 and interval
                $add++;
                $ttime = strtotime("+" . $add . " " . $interval, $time1);
                $looped++;
            }

            $time1 = strtotime("+" . $looped . " " . $interval, $time1);
            $diffs[$interval] = $looped;
            if($looped>0) break;
        }

        $count = 0;
        $times = array();

        // Loop thru all diffs
        foreach ($diffs as $interval => $value) {
            // Break if we have needed precission
            if ($count >= $precision) {
                break;
            }
            // Add value and interval 
            // if value is bigger than 0
            if ($value > 0) {
                // Add s if value is not 1
                /*if ($value != 1) {
                    $interval .= "s";
                }*/

                // Add value and interval to times array
                $times[] = $value . " " . $intervals_indo[array_search($interval, $intervals)] . " lalu";
                $count++;
            }
        }

        // Return string with times
        return implode(", ", $times);
    }

    public function index() {
		$model = new \App\Models\Youtube();

		$page     = $this->request->getVar('page');
        $id_grup  = $this->request->getVar('id_grup');
        $limit    = 50;
        $start    = $limit*($page-1);
		
		//YOUTUBE
		$builder = $model->select('
            id, 
            title, 
            tanggal_create, 
            \'\' konten, 
            link_download AS download,
            banner, 
            thumbnail,
            videoId, 
            duration
        ');
		
        $builder->where(['is_aktif' => 1]);
		$builder->orderBy('id', 'DESC');
		$builder->limit($limit, $start);
		//$data = $builder->getCompiledSelect();
		$data = $builder->get()->getResultArray();
        $totalData = 0;
        if($data) {
            foreach($data as $key => $value) {
                $data[$key]["tanggal"] = $this->dateDiff(date("Y-m-d H:i:s"), $value["tanggal_create"]);
        
            }
            $totalData = ($limit*($page-1));
            $page++;
        }
		
		//TOTAL MEMBER
		$builder->select('COUNT(*) total');
        $builder->where(['is_aktif' => 1]);
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
