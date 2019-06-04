<?php

namespace Ruima\MicroserviceTool;
use Laravel\Lumen\Application;
use GuzzleHttp\Client;
use Ruima\MicroserviceTool\Console\Commands\HeartBeat;

class Slaver {

    public $name;
    public $type;
    public $description;
    public $version;
    public $url;
    public $route = [];
    static $conf_path = __DIR__.'/MicroServer.json';

    /**
     * Slaver constructor.
     */
    public function __construct($app)
    {
        // dd($app);
        $route = $app->router->getRoutes();
        $route_list = array_map( function ($el) {
            $el['reg'] = preg_replace('/\/\{(\w+?)\}/', '/\w+', $el['uri'], -1, $el['params']);

            $el['auth'] = isset($el['action']) && isset($el['action']['middleware']) && array_search('auth', $el['action']['middleware']) !== false;

            unset($el['action']);
            return $el;
        }, $route);
        $this->name = env('MICROSERVICE_NAME');
        $this->type = env('MICROSERVICE_TYPE');
        $this->description = env('MICROSERVICE_DESCRIPTION', '');
        $this->version = env('MICROSERVICE_VERSION', $app->version());
        $this->url = $_SERVER['SERVER_NAME'] ?? env('APP_URL');
        $this->route = $route_list;
    }

    /**
     * @Descripttion: 获取当前微服务各项参数
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public function getSlaverInfo()
    {
        # code...
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'version' => $this->version,
            'url' => $this->url,
            'route' => $this->route,
        ];
    }

    /**
     * @Descripttion: 预设向微服务发起销毁网关缓存的用户信息的方法
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public function destoryAuth($sort_token = null, $gateway_url = null)
    {
        # code...
        $request = \Illuminate\Http\Request::capture();
        if (is_null($gateway_url)) {
            $gateway_url = json_decode($request->input('service_config'), true)['auth'];
        }
        if (is_null($sort_token)) {
            $token = $request->headers->get('authorization');
            if (is_null($token)) {
                return null;
            }
            $sort_token = substr($token, -64, 64);
        }
        $http = new \GuzzleHttp\Client();
        $response = $http->delete($gateway_url.'/distroy-auth', [
            'query' => [
                'sort_token' => $sort_token,
            ]
        ]);
        $result = json_decode((string) $response->getBody(), true) ?: $response->getBody();
        return response($result, $response->getStatusCode());
    }

    /**
     * @Descripttion: 为微服务注册预设路由的方法
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public static function routes($callback = null, array $options = [])
    {
      # code...
      
      if ($callback instanceof Application && preg_match('/5\.[5-7]\..*/', $callback->version())) $callback = $callback->router;

      $callback = $callback ?: function ($router) {
          $router->all();
      };

      $defaultOptions = [
          // 'prefix' => 'oauth',
          'namespace' => 'Ruima\MicroserviceTool\Controllers',
      ];

      $options = array_merge($defaultOptions, $options);

      $callback->group(array_except($options, ['namespace']), function ($router) use ($callback, $options) {
          $routes = new RouteRegistrar($router, $options);
          $routes->slaver();
      });

    }

    /**
     * @Descripttion: 根据传入微服务名获取对应的url返回
     * @Author: LeungYin
     * @param {type} 
     * @return: String $url
     */
    public function getServerUrl(String $service_name)
    {
        # code...
        $service_list = json_decode(file_get_contents(self::$conf_path), true);
        if (isset($service_list[$service_name])) {
            return $service_list[$service_name];
        } else {
            $service_list = HeartBeat::handle();
            if (isset($service_list[$service_name])) {
                file_put_contents(self::$conf_path, $service_list);
                return $service_list[$service_name];
            } else {
                throw new \Exception('can not find server!');
            }
        }

    }

    /** 对外提供请求方法的基础方法
     * @Descripttion: 
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    private function request(String $method, String $url, Array $guzzle_config)
    {
        # code...
        $http = new Client();
        $response = $http->request($method, $url, $guzzle_config);
        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @Descripttion: 对外提供get接口，传入目标微服务名，请求路由，设置参数。由于底层使用Guzzle，设置参数可参考Guzzle文档。
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public function get(String $service_name, String $url, Array $guzzle_config)
    {
        # code...
        $target_url = $this->getServerUrl($service_name);
        return $this->request('GET', $target_url.$url, $guzzle_config);
    }

    /**
     * @Descripttion: 对外提供post接口，传入目标微服务名，请求路由，设置参数。由于底层使用Guzzle，设置参数可参考Guzzle文档。
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public function post(String $service_name, String $url, Array $guzzle_config)
    {
        $target_url = $this->getServerUrl($service_name);
        return $this->request('POST', $target_url.$url, $guzzle_config);
    }

    /**
     * @Descripttion: 对外提供delete接口，传入目标微服务名，请求路由，设置参数。由于底层使用Guzzle，设置参数可参考Guzzle文档。
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public function delete(String $service_name, String $url, Array $guzzle_config)
    {
        $target_url = $this->getServerUrl($service_name);
        return $this->request("DELETE", $target_url.$url, $guzzle_config);
    }

    /**
     * @Descripttion: 对外提供put接口，传入目标微服务名，请求路由，设置参数。由于底层使用Guzzle，设置参数可参考Guzzle文档。
     * @Author: LeungYin
     * @param {type} 
     * @return: 
     */
    public function put(String $service_name, String $url, Array $guzzle_config)
    {
        $target_url = $this->getServerUrl($service_name);
        return $this->request('PUT', $target_url.$url, $guzzle_config);
    }
}