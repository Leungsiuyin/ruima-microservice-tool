<?php
namespace Ruima\MicroserviceTool\Console\Commands;

use GuzzleHttp\Client;

class HeartBeat
{

    static $conf_path = __DIR__.'/../../MicroServer.json';
    
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
        $response = $http->post($url.'/registe-microserver', [
            'json' => $data
        ]);
        // $code = $response->getStatusCode();
        // $reason = $response->getReasonPhrase();
        $body = $response->getBody();
        file_put_contents(self::$conf_path, $body);
        // dd(json_decode(file_get_contents(self::$conf_path), true));
        return $body;
        // return response()->json(json_decode($response->getBody(), true));
        // return response()->json($data);
        
    }
}
