<?php

namespace Ruima\MicroserviceTool\Services;

use Illuminate\Support\Facades\Redis;

class MicroAuthConfigService
{
    // return json_decode(Redis::get('micro_server_conf'), true);
    // Redis::set('micro_server_conf', $conf);

    /**
     * @Descripttion: 通过Token获取用户认证信息
     * @Author: LeungYin
     * @param String $token
     * @return: Array AuthInfo
     */
    public static function getAuthInfo(String $token)
    {
        # code...
        $info = self::loadAuthInfo($token);

        if (!is_null($info)) {
          return $info;
        }
        
        $info = self::getAuthFromMicro($token);
        if (!is_null($info)) {
          Redis::set('Auth:' . substr($token, -64, 64), json_encode($info));
        }

        return $info;
    }

    /**
     * @Descripttion: 从Redis获取Auth信息
     * @Author: LeungYin
     * @param String $token
     * @return: Array AuthInfo
     */
    private static function loadAuthInfo(String $token)
    {
        # code...
        return json_decode(Redis::get('Auth:' . substr($token, -64, 64)));

    }


    /**
     * @Descripttion: 从auth微服务获取auth信息
     * @Author: LeungYin
     * @param String $token
     * @return: Array AuthInfo
     */
    private static function getAuthFromMicro($token)
    {
        # code...
        $config = MicroserverConfigService::loadMircoserviceConfig()['auth'];
        if (is_null($config)) {
            throw new \Exception('can not find auth server!');
        }
        $auth_url = $config['url'];

        $http = new \GuzzleHttp\Client();
        $response = $http->get($auth_url . '/auth-info', [
            'headers' => [
                'Authorization' => $token
            ],
            'query' => [
                'simple_info' => true
            ]
        ]);
        $auth_info_json = $response->getBody();
        $auth_info = json_decode($auth_info_json, true);
        return $auth_info;
    }

    /**
     * @Descripttion: 删除redis中缓存的auth信息，在下次请求时重新从auth微服务获取新的auth信息
     * @Author: LeungYin
     * @param String $token
     * @return: 
     */
    static public function distroyAuthToken($token)
    {
      # code...
    //   dd($token);
      if ($token !== 'all') {
          return Redis::del('Auth:' . substr($token, -64, 64));
      } else {
          $list = Redis::keys('Auth:*');
          foreach ($list as $key => $value) {
              # code...
              Redis::del($value);
          }
      }
    }

}
