<?php

namespace Ruima\MicroserviceTool;
use Laravel\Lumen\Application;

class Master {

    public $name;
    public $type;
    public $description;
    public $version;
    public $url;
    public $route = [];

    /**
     * Master constructor.
     */
    public function __construct($app)
    {
        // dd($app);
        // $route = $app->router->getRoutes();
        // $route_list = array_map( function ($el) {
        //     $el['reg'] = preg_replace('/\/\{(\w+?)\}/', '/\w+', $el['uri'], -1, $el['params']);

        //     $el['auth'] = isset($el['action']) && isset($el['action']['middleware']) && array_search('auth', $el['action']['middleware']) !== false;

        //     unset($el['action']);
        //     return $el;
        // }, $route);
        // $this->name = env('MICROSERVICE_NAME');
        // $this->type = env('MICROSERVICE_TYPE');
        // $this->description = env('MICROSERVICE_DESCRIPTION', '');
        // $this->version = env('MICROSERVICE_VERSION', $app->version());
        // $this->url = $_SERVER['SERVER_NAME'] ?? env('APP_URL');
        // $this->route = $route_list;
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