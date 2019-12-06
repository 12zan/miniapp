<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    /*
     * 默认配置，将会合并到各模块中
     */
    'defaults' => [
        /*
         * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
         */
        'response_type' => 'array',

        /*
         * 使用 Laravel 的缓存系统
         */
        'use_laravel_cache' => true,

        /*
         * 日志配置
         *
         * level: 日志级别，可选为：
         *                 debug/info/notice/warning/error/critical/alert/emergency
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        'log' => [
            'level' => env('WECHAT_LOG_LEVEL', 'debug'),
            'file' => env('WECHAT_LOG_FILE', storage_path('logs/wechat.log')),
        ],
    ],

    /*
     * 路由配置
     */
    'route' => [
        /*
         * 开放平台第三方平台路由配置
         */
        // 'open_platform' => [
        //     'uri' => 'serve',
        //     'action' => Overtrue\LaravelWeChat\Controllers\OpenPlatformController::class,
        //     'attributes' => [
        //         'prefix' => 'open-platform',
        //         'middleware' => null,
        //     ],
        // ],
    ],

    /*
     * 公众号
     */
    'official_account' => [
        'default' => [
            'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'wxac19b0c86b283ed8'),// AppID
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'e90b981d514c65042c4c54cc0becf161'),    // AppSecret
            'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'yuanli'),           // Token
            'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', '13421307fc4b5679d7917b065d757bc6c17e555e5d2'),                 // EncodingAESKey

            /*
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址(如果使用中间件，则随便填写。。。)
             */
            'oauth' => [
                'scopes'   => array_map('trim', explode(',', env('WECHAT_OFFICIAL_ACCOUNT_OAUTH_SCOPES', 'snsapi_base'))),
                'callback' => env('WECHAT_OFFICIAL_ACCOUNT_OAUTH_CALLBACK', '/api/login/oauth-callback'),
            ],
        ],
    ],

    /*
     * 开放平台第三方平台
     */
    // 'open_platform' => [
    //     'default' => [
    //         'app_id'  => env('WECHAT_OPEN_PLATFORM_APPID', ''),
    //         'secret'  => env('WECHAT_OPEN_PLATFORM_SECRET', ''),
    //         'token'   => env('WECHAT_OPEN_PLATFORM_TOKEN', ''),
    //         'aes_key' => env('WECHAT_OPEN_PLATFORM_AES_KEY', ''),
    //     ],
    // ],

    /*
     * 小程序
     */
    // 'mini_program' => [
    //     'default' => [
    //         'app_id'  => env('WECHAT_MINI_PROGRAM_APPID', ''),
    //         'secret'  => env('WECHAT_MINI_PROGRAM_SECRET', ''),
    //         'token'   => env('WECHAT_MINI_PROGRAM_TOKEN', ''),
    //         'aes_key' => env('WECHAT_MINI_PROGRAM_AES_KEY', ''),
    //     ],
    // ],

    /*
     * 微信支付
     */
    'payment' => [
        'default' => [
            'sandbox'            => env('WECHAT_PAYMENT_SANDBOX', false),
            'app_id'             => env('WECHAT_PAYMENT_APPID', 'wxac19b0c86b283ed8'),
            'mch_id'             => env('WECHAT_PAYMENT_MCH_ID', '1511250111'),
            'key'                => env('WECHAT_PAYMENT_KEY', 'KRa1k1ROhGReAJHWZ8uUQTIILyfZTyyZ'),
            'cert_path'          => env('WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'),    // XXX: 绝对路径！！！！
            'key_path'           => env('WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'),      // XXX: 绝对路径！！！！
            'notify_url'         => 'http://beiwen.z.12zan.net/api/pay/wxpay/notify',                           // 默认支付结果通知地址
        ],
        // ...
    ],

    /*
     * 企业微信
     */
    // 'work' => [
    //     'default' => [
    //         'corp_id' => 'xxxxxxxxxxxxxxxxx',
    ///        'agent_id' => 100020,
    //         'secret'   => env('WECHAT_WORK_AGENT_CONTACTS_SECRET', ''),
    //          //...
    //      ],
    // ],
];
