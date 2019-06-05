<?php
namespace Ruima\MicroserviceTool\Console\Commands;

use \Carbon\Carbon;
use Illuminate\Console\Command;
use Ruima\MicroserviceTool\Services\MicroserverConfigService;

class HeartBeatCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microserver:heart-beat-check';

    /**
     * The console command description.
     *
     * @var string roll something back to somewhere
     */
    protected $description = 'check the mircroserver info in gateway';

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
        $conf = MicroserverConfigService::loadMircoserviceConfig(true);
        if (!$conf) {
            //TODO 读取配置文件失败
        }
        // 对数据微服务进行检查
        if (!isset($conf['update_at'])) {
            $conf['update_at'] = Carbon::now();
        }
        $last_check_time = Carbon::create($conf['update_at']) ;
        foreach (['base_service', 'data_service'] as $service_type) {
            # code...
            foreach ($conf[$service_type] as $key => &$value) {
                # code...
                if (!isset($value['update_at'])) {
                    $value['update_at'] = Carbon::now();
                }
                $last_beat_time = Carbon::create($value['update_at']) ;
                $diff = $last_beat_time->diffInMinutes($last_check_time);
                if ($diff > 5) {
                    $value['status'] = 'inactive';
                    $value['route_list'] = [];
                }
            }
        }
        $conf['update_at'] = Carbon::now()->toDateTimeString();

        if (MicroserverConfigService::setMircoserviceConfig(json_encode($conf))) {
            // TODO write conf success
            return $conf;
        }
        // TODO write conf fail
    }
}
