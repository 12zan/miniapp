<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\OpenPlatform;

use EasyWeChat\Kernel\ServiceContainer;
use EasyWeChat\MiniProgram\Encryptor;
use EasyWeChat\OpenPlatform\Authorizer\Auth\AccessToken;
use EasyWeChat\OpenPlatform\Authorizer\MiniProgram\Application as MiniProgram;
use EasyWeChat\OpenPlatform\Authorizer\MiniProgram\Auth\Client;
use EasyWeChat\OpenPlatform\Authorizer\OfficialAccount\Account\Client as AccountClient;
use EasyWeChat\OpenPlatform\Authorizer\OfficialAccount\Application as OfficialAccount;
use EasyWeChat\OpenPlatform\Authorizer\OfficialAccount\OAuth\ComponentDelegate;
use EasyWeChat\OpenPlatform\Authorizer\Server\Guard;

/**
 * Class Application.
 *
 * @property \EasyWeChat\OpenPlatform\Server\Guard        $server
 * @property \EasyWeChat\OpenPlatform\Auth\AccessToken    $access_token
 * @property \EasyWeChat\OpenPlatform\CodeTemplate\Client $code_template
 *
 * @method mixed handleAuthorize(string $authCode = null)
 * @method mixed getAuthorizer(string $appId)
 * @method mixed getAuthorizerOption(string $appId, string $name)
 * @method mixed setAuthorizerOption(string $appId, string $name, string $value)
 * @method mixed getAuthorizers(int $offset = 0, int $count = 500)
 * @method mixed createPreAuthorizationCode()
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
        Base\ServiceProvider::class,
        Server\ServiceProvider::class,
        CodeTemplate\ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $defaultConfig = [
        'http' => [
            'timeout' => 5.0,
            'base_uri' => 'https://api.weixin.qq.com/',
        ],
    ];

    /**
     * Creates the officialAccount application.
     *
     * @param string                                                    $appId
     * @param string|null                                               $refreshToken
     * @param \EasyWeChat\OpenPlatform\Authorizer\Auth\AccessToken|null $accessToken
     *
     * @return \EasyWeChat\OpenPlatform\Authorizer\OfficialAccount\Application
     */
    public function officialAccount(string $appId, string $refreshToken = null, AccessToken $accessToken = null): OfficialAccount
    {
        $application = new OfficialAccount($this->getAuthorizerConfig($appId, $refreshToken), $this->getReplaceServices($accessToken) + [
            'encryptor' => $this['encryptor'],

            'account' => function ($app) {
                return new AccountClient($app, $this);
            },
        ]);

        $application->extend('oauth', function ($socialite) {
            /* @var \Overtrue\Socialite\Providers\WeChatProvider $socialite */
            return $socialite->component(new ComponentDelegate($this));
        });

        return $application;
    }

    /**
     * Creates the miniProgram application.
     *
     * @param string                                                    $appId
     * @param string|null                                               $refreshToken
     * @param \EasyWeChat\OpenPlatform\Authorizer\Auth\AccessToken|null $accessToken
     *
     * @return \EasyWeChat\OpenPlatform\Authorizer\MiniProgram\Application
     */
    public function miniProgram(string $appId, string $refreshToken = null, AccessToken $accessToken = null): MiniProgram
    {
        return new MiniProgram($this->getAuthorizerConfig($appId, $refreshToken), $this->getReplaceServices($accessToken) + [
            'encryptor' => function () {
                return new Encryptor($this['config']['app_id'], $this['config']['token'], $this['config']['aes_key']);
            },

            'auth' => function ($app) {
                return new Client($app, $this);
            },
        ]);
    }

    /**
     * Return the pre-authorization login page url.
     *
     * @param string      $callbackUrl
     * @param string|null $authCode
     *
     * @return string
     */
    public function getPreAuthorizationUrl(string $callbackUrl, string $authCode = null): string
    {
        $queries = [
            'component_appid' => $this['config']['app_id'],
            'pre_auth_code' => $authCode ?: $this->createPreAuthorizationCode()['pre_auth_code'],
            'redirect_uri' => $callbackUrl,
        ];

        return 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?'.http_build_query($queries);
    }

    /**
     * @param string      $appId
     * @param string|null $refreshToken
     *
     * @return array
     */
    protected function getAuthorizerConfig(string $appId, string $refreshToken = null): array
    {
        return [
            'debug' => $this['config']->get('debug', false),
            'response_type' => $this['config']->get('response_type'),
            'log' => $this['config']->get('log', []),
            'app_id' => $appId,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * @param \EasyWeChat\OpenPlatform\Authorizer\Auth\AccessToken|null $accessToken
     *
     * @return array
     */
    protected function getReplaceServices(AccessToken $accessToken = null): array
    {
        $services = [
            'access_token' => $accessToken ?: function ($app) {
                return new AccessToken($app, $this);
            },

            'server' => function ($app) {
                return new Guard($app);
            },
        ];

        foreach (['cache', 'http_client', 'log', 'logger', 'request'] as $reuse) {
            if (isset($this[$reuse])) {
                $services[$reuse] = $this[$reuse];
            }
        }

        return $services;
    }

    /**
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this['base'], $method], $args);
    }
}
