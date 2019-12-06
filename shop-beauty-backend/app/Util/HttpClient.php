<?php

namespace App\Util;

use GuzzleHttp\Client;

class HttpClient
{
    public $results="";

    public function postRaw($url, $rawData){
        $client = new Client([
            'timeout'  => 4.0
        ]);
        try {
            $response = $client->request('POST', $url, ['body' => $rawData]);
        } catch (\Exception $e) {
            \Log::error('post '.$url.' error:'.$e->getMessage());
            throw new \ApiCustomException("系统繁忙", 502);
        }

        $code = $response->getStatusCode();

        if($code!=200){
            return "";
        }

        $this->results = $response->getBody();

        return  $this->results ;
    }

    public function postForm($url, $formData){
        $client = new Client([
            'timeout'  => 4.0
        ]);
        try {
            $response = $client->request('POST', $url, ['form_params' => $formData]);
        } catch (\Exception $e) {
            \Log::error('post '.$url.' error:'.$e->getMessage());
            throw new \ApiCustomException("系统繁忙", 502);
        }
        $code = $response->getStatusCode();
        if($code!=200){
            return "";
        }
        $this->results = $response->getBody();
        return  $this->results ;
    }

    public function getRaw($url, $rawData)
    {
        $client = new Client([
            'timeout'  => 4.0
        ]);
        try {
            $response = $client->request('GET', $url, ['body' => $rawData]);
        } catch (\Exception $e) {
            \Log::error('get '.$url.' error:'.$e->getMessage());
            throw new \ApiCustomException("系统繁忙", 502);
        }
        $code = $response->getStatusCode();
        if($code!=200){
            return "";
        }
        $this->results = $response->getBody()->getContents();
        return  json_decode($this->results, true);
    }

    public function getQuery($url, $rawData, $isThrow = true)
    {
        $client = new Client([
            'timeout'  => 4.0
        ]);
        try {
            $response = $client->request('GET', $url, ['query' => $rawData]);
        } catch (\Exception $e) {
            if($isThrow){
                \Log::error('get '.$url.' error:'.$e->getMessage());
                throw new \ApiCustomException("系统繁忙", 502);
            }
            return false;
        }

        $code = $response->getStatusCode();

        if( $code != 200) {
            return false;
        }

        $this->results = $response->getBody()->getContents();

        return  json_decode($this->results, true);
    }
}