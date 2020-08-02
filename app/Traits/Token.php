<?php
namespace App\Traits;

trait Token {

    /** 
     * token Generator
     */
    public function createToken() {
        $token = openssl_random_pseudo_bytes(16);
        $token = bin2hex($token);
        return $token;
    }

    /** randomCode generator
     * @param length<int>
     */
    private function randomCode($length = 6) {
        $chars =  '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = strlen($chars) - 1;
        for ($i=0; $i < $length; $i++)
        {
            $str .= $chars[random_int(0, $max)];
        }
        return $str;
    }

}