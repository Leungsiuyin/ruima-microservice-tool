<?php
namespace Ruima\MicroserviceTool\Console\Commands;

use GuzzleHttp\Client;

class HeartBeatCheck
{

    /**Create a new command instance.
     * HealthCheck constructor.
     * @param HealthCheckervice $HealthCheckervice
     */
    public function __construct()
    {
        parent::__construct();
        // $this->MicroserverConfigService = new MicroserverConfigService();
        // self::handle();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public static function handle()
    {
        # code...
        $http = new Client();
        $data = app('MicroserviceTool')->getSlaverInfo();
        $url = env('MICROSERVICE_GATEWAY_URL');
        $response = $http->post($url.'/', []);
        return $response->getBody();
        
    }
}
