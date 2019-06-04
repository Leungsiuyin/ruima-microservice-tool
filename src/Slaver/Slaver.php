<?php

namespace Ruima\MicroserviceTool;
use Laravel\Lumen\Application;

class Slaver {

    public $name;
    public $type;
    public $description;
    public $version;
    public $url;
    public $route = [];

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
        $this->url = $_SERVER['SERVER_NAME'];
        $this->route = $route_list;
    }

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
          $routes->all();
      });

    }

}