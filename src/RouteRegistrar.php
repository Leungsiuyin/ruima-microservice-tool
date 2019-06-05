<?php

namespace Ruima\MicroserviceTool;

class RouteRegistrar
{
    /**
     * @var Lumen Router
     */
    private $router;

    /**
     * @var array
     */
    private $options;

    /**
     * Create a new route registrar instance.
     *
     * @param  $router
     * @param  array $options
     */
    public function __construct($router, array $options = [])
    {
        $this->router = $router;
        $this->options = $options;
    }

    /**
     * Register routes for slaver
     *
     * @return void
     */
    public function slaver()
    {
        $this->healthCheck();
        $this->heartBeat();
    }

    /**
     * Register routes for master
     *
     * @return void
     */
    public function master()
    {
        $this->healthCheckMaster();
        $this->heartBeatMaster();
        $this->registerMicroserver();
        $this->distroyAuth();
        $this->distribute();
    }

    /**
     * @param string $path
     * @return string
     */
    private function prefix($path)
    {
        if (strstr($path, '\\') === false && isset($this->options['namespace'])) return $this->options['namespace'] . '\\' . $path;

        return $path;
    }

    public function healthCheck()
    {
      # code...
      $this->router->get('/health-check', $this->prefix('SalverController@healthCheck'));
    }

    public function heartBeat()
    {
      # code...
      $this->router->get('/heart-beat', $this->prefix('SalverController@heartBeat'));
    }

    public function healthCheckMaster()
    {
      # code...
      $this->router->get('/health-check', $this->prefix('MasterController@healthCheck'));
    }

    public function heartBeatMaster()
    {
      # code...
      $this->router->get('/heart-beat-check', $this->prefix('MasterController@heartBeatCheck'));
    }

    public function registerMicroserver()
    {
      # code...
      $this->router->post('/register-microserver', $this->prefix('MasterController@registerMicroserver'));
    }

    public function distroyAuth()
    {
      # code...
      $this->router->delete('/distroy-auth', $this->prefix('MasterController@distroyAuth'));
    }
    
    public function distribute()
    {
      # code...
      $this->router->get('{path:.*}', $this->prefix('MasterController@distribute'));
      $this->router->post('{path:.*}', $this->prefix('MasterController@distribute'));
      $this->router->put('{path:.*}', $this->prefix('MasterController@distribute'));
      $this->router->patch('{path:.*}', $this->prefix('MasterController@distribute'));
      $this->router->delete('{path:.*}', $this->prefix('MasterController@distribute'));
      $this->router->options('{path:.*}', $this->prefix('MasterController@distribute'));
    }
}
