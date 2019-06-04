<?php
namespace Ruima\MicroserviceTool\Console\Commands;

use GuzzleHttp\Client;

class HeartBeat
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
    static public function handle()
    {
        # code...
        $http = new Client();
        $data = app('MicroserviceTool')->getSlaverInfo();
        echo '111';
        
    }
}
