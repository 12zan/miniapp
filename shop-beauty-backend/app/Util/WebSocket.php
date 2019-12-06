<?php

namespace App\Util;

use GuzzleHttp\Client;

/**
 * websocket 通信
 */
class WebSocket
{

    public static function pushData($roomId, $data = [])
    {
        $client = new Client([
            'base_uri' => env('WS_PUSH_URL'),
            'timeout'  => 6.0,
        ]);

        //推送点餐
        $client->request('POST', env('APP_NAME').'/'.$roomId, [
            'form_params' => [
                'data'  => json_encode($data)
            ],
            'http_erros' => false
        ]);
    }

}