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
     * Register routes for master+
     *
     * @return void
     */
    public function master()
    {
        // $this->healthCheck();
        // $this->heartBeat();
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
}
