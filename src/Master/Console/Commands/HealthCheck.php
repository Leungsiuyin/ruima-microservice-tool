<?php

namespace Ruima\MicroserviceTool\Console\Commands;

use Illuminate\Console\Command;
use Ruima\MicroserviceTool\Services\MicroserverConfigService;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microserver:health-check';

    /**
     * The console command description.
     *
     * @var string roll something back to somewhere
     */
    protected $description = 'check the mircroserver status';

    private $MicroserverConfigService;

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
    static public function handle()
    {
        # code...
        $conf = MicroserverConfigService::loadMircoserviceConfig(true);
        // return $conf;
        if (!$conf) {
            //TODO 读取配置文件失败
        }
        // 对基础微服务进行检查
        $base_service_http = new \GuzzleHttp\Client();
        $base_service_promises = [];
        foreach ($conf['base_service'] as $key => $value) {
            # code...
            $base_service_promises[$key] = $base_service_http->getAsync($value["url"].'/health-check',[
             'http_errors' => false
        ]);
        }
        $base_service_results = \GuzzleHttp\Promise\unwrap($base_service_promises);
        foreach ($base_service_results as $key => $value) {
            # code...
            $health_info = json_decode($value->getBody(),true);
            try {
                //code...
                if (is_null($health_info)){
                    throw(new \Exception('health check fail'));
                };
                if ($key !== $health_info['type']) {
                    throw(new \Exception('health check type change'));
                }
                $conf['base_service'][$key]['status'] = 'active';
                $conf['base_service'][$key]['name'] = $health_info['name'];
                $conf['base_service'][$key]['description'] = $health_info['description'];
                $conf['base_service'][$key]['route_list'] = $health_info['route'];
            } catch (\Throwable $th) {
                // throw $th;
                $conf['base_service'][$key]['status'] = 'inactive';
            }
        }
        // 对数据微服务进行检查
        $data_service_http = new \GuzzleHttp\Client();
        $data_service_promises = [];
        foreach ($conf['data_service'] as $key => $value) {
            # code...
            $data_service_promises[$key] = $data_service_http->getAsync($value["url"].'/health-check',[
             'http_errors' => false
        ]);
        }
        $data_service_results = \GuzzleHttp\Promise\unwrap($data_service_promises);
        foreach ($data_service_results as $key => $value) {
            # code...
            $health_info = json_decode($value->getBody(),true);
            try {
                //code...
                if (is_null($health_info)){
                    throw(new \Exception('health check fail'));
                };
                if ('data' !== $health_info['type']) {
                    throw(new \Exception('health check type change'));
                }
                $conf['data_service'][$key]['status'] = 'active';
                $conf['data_service'][$key]['name'] = $health_info['name'];
                $conf['data_service'][$key]['description'] = $health_info['description'];
                $conf['data_service'][$key]['route_list'] = $health_info['route'];
            } catch (\Throwable $th) {
                //throw $th;
                $conf['data_service'][$key]['status'] = 'inactive';
            }
        }
        $conf['update_at'] = \Carbon\Carbon::now()->toDateTimeString();
        $json_conf = preg_replace_callback("#\\\u([0-9a-f]{1,4}+)#i", function ($matches) {
            return iconv('UCS-2BE', 'UTF-8', pack('H4', $matches[1]));
        }, json_encode($conf));
        if (MicroserverConfigService::setMircoserviceConfig($json_conf)) {
            // TODO write conf success
            return $conf;
        }
        // TODO write conf fail
        // return self::response->withSuccess($conf);
    }
}
