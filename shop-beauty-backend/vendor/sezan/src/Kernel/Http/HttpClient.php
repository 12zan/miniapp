<?php

namespace SeZan\Kernel\Http;

use GuzzleHttp\Client;
use SeZan\Kernel\Exceptions\HttpException;

class HttpClient
{

    public $client;
    public $response;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 2.0]);
    }

    public function post($url, $data = null)
    {
        try {
           $this->response = $this->client->request('POST', $url, [
                'form_params' => $data
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new HttpException("post $url error", $e->getCode());
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new HttpException("post $url error", $e->getCode());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new HttpException("post $url error", $e->getCode());
        }

       return  $this;
    }

    public function get($url, $data = null)
    {
        try {
            $this->response = $this->client->request('GET', $url, [
                'query'       => $data,
                'headers' => [
                    'Accept'     => 'application/json'
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new HttpException("post $url error", $e->getCode());
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new HttpException("post $url error", $e->getCode());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new HttpException("post $url error", $e->getCode());
        }

        return $this;
    }

    public function getResponse()
    {
        return $this->response->getBody()->getContents();
    }


}