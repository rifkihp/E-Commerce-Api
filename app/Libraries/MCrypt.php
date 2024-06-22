<?php
namespace App\Libraries;

use CodeIgniter\Library;

class MCrypt
{
    protected $iv     = 'fedcba9876543210';
    protected $key    = '0123456789abcdef'; 
    protected $method = 'AES-256-CBC';

    /**
     * @param string $str
     * @param bool $isBinary whether to encrypt as binary or not. Default is: false
     * @return string Encrypted data
     */
    public function encrypt($str, $isBinary = false)
    {
        $key = hash('sha256', $this->key);
        $iv = substr(hash('sha256', $this->iv), 0, 16);
        $output = openssl_encrypt($str, $this->method, $key, 0, $iv);
        
        return base64_encode($output);
    }

    /**
     * @param string $code
     * @param bool $isBinary whether to decrypt as binary or not. Default is: false
     * @return string Decrypted data
     */
    public function decrypt($code, $isBinary = false)
    {
        
        $key = hash('sha256', $this->key);
        $iv = substr(hash('sha256', $this->iv), 0, 16);
        $output = openssl_decrypt(base64_decode($code), $this->method, $key, 0, $iv);
        
        return $output;
    }

    protected function hex2bin($hexdata)
    {
        $bindata = '';

        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }

        return $bindata;
    }

}