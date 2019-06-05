<?php
namespace Ruima\MicroserviceTool\Console\Commands;

use GuzzleHttp\Client;

class HeartBeat
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microserver:heart-beat';

    /**
     * The console command description.
     *
     * @var string roll something back to somewhere
     */
    protected $description = 'send the mircroserver info to gateway';

    static $conf_path = __DIR__.'/../../MicroServer.json';
    
    /**Create a new command instance.
     * HealthCheck constructor.
     * @param HealthCheckervice $HealthCheckervice
     */
    public function __construct()
    {
        parent::__construct();
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
        $response = $http->post($url.'/register-microserver', [
            'json' => $data
        ]);
        $body = $response->getBody();
        file_put_contents(self::$conf_path, $body);
        return $body;
        
    }
}
