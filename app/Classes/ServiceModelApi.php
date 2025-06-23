<?php
namespace App\Classes;

use App\Utility\Status;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ServiceModelApi
{
    private const GET_METHOD = "GET";
    private const POST_METHOD = "POST";

    /* ----------------------- Get Data From Service Model ---------------------- */

    private static function serviceModelDataByToken($token=null, $method, $path, $fieldArray)
    {
        try {
            $curl = curl_init();
            $config=array(
                CURLOPT_URL => env('SERVICE_MODEL_DOMAIN') . '' . $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $fieldArray,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded', 'Connection: Keep-Alive' )

            );
            if($token!=null){
                array_push( $config,
                "CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                )"
             );
            }
            curl_setopt_array($curl, $config);
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
}
