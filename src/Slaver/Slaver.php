<?php

namespace Ruima\MicroserviceTool;

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
    public function __construct($name, $type, $url, $description = '', $version = '', Array $route = null)
    {
      
        $route_list = array_map( function ($el) {
            $el['reg'] = preg_replace('/\/\{(\w+?)\}/', '/\w+', $el['uri'], -1, $el['params']);

            $el['auth'] = isset($el['action']) && isset($el['action']['middleware']) && array_search('auth', $el['action']['middleware']) !== false;

            unset($el['action']);
            return $el;
        }, $route);
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->version = $version;
        $this->url = $url;
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

    public function routes($callback = null, array $options = [])
    {
      # code...
      
      if ($callback instanceof Application && preg_match('/5\.[5-7]\..*/', $callback->version())) $callback = $callback->router;

      $callback = $callback ?: function ($router) {
          $router->all();
      };

      $defaultOptions = [
          // 'prefix' => 'oauth',
          'namespace' => '\MicroserviceTool\Controllers',
      ];

      $options = array_merge($defaultOptions, $options);

      $callback->group(array_except($options, ['namespace']), function ($router) use ($callback, $options) {
          $routes = new RouteRegistrar($router, $options);
          $routes->all();
      });

    }

}