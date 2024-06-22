<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Splashscreen extends BaseController
{
	use ResponseTrait;

	public function index()
	{
		//
	}

	public function getSplashscreen()
    {	
		$id = $this->request->getVar('id');
		$model = new \App\Models\Splashscreen();
		$umum  = $model->getUmum($id);

		if($umum) {
            $response = [
                'success'      => true,			
    			'splashscreen' => $umum[0]
            ];
			return $this->respond($response, 200);
		} else {
			$response = [
                'success' => false,
				'message' => 'Data umum tidak ditemukan.'
            ];
            return $this->respond($response, 500);	
		}
    }
}
