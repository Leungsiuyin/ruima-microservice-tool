<?php

namespace Ruima\MicroserviceTool\Services;

use Illuminate\Support\Facades\Redis;

class MicroserverConfigService
{
    static public function configPath()
    {
        # code...
        return env('MICROSERVICE_CONFIG_DIR', __DIR__.'/../../../../../../config/MicroServer.json');
    }
    
    /**
     * @Descripttion: 从MicroServer.json文件读取微服务config
     * @Author: LeungYin
     * @param Null
     * @return: Array config
     */
    static private function loadConfFile()
    {
        # code...
        if (!file_exists(self::configPath())) {
            return null;
        }
        try {
            //code...
            // 从文件中读取数据到PHP变量
            $conf_file = file_get_contents(self::configPath());
        } catch (\Throwable $th) {
            //throw $th;
            // TODO 配置文件不存在
        }
        
        return json_decode($conf_file, true);
    }

    /**
     * @Descripttion: 从Redis读取微服务config
     * @Author: LeungYin
     * @param Null
     * @return: Array config
     */
    static private function loadCongRedis()
    {
        # code...
        return json_decode(Redis::get('micro_server_conf'), true);
    }

    /**
     * @Descripttion: 检查config有效性，是否符合约定的格式
     * @Author: LeungYin
     * @param {type} 
     * @return: Boolean
     */
    static private function checkConf($conf = null)
    {
        # code...
        if (
            !is_null($conf) &&
            array_key_exists("base_service", $conf) &&
            array_key_exists("data_service", $conf)
        ) {
            // 配置文件完整
            return true;
        }
        // 配置文件不完整
        return false;
    }

    /**
     * @Descripttion: 读取微服务配置config
     * @Author: LeungYin
     * @param [$for_health_check = false]
     * @return: Array config
     */
    static public function loadMircoserviceConfig($for_health_check = false, $simple_info = false)
    {
        # code...
        if ($for_health_check) {
            $conf = self::loadConfFile();
        } else {
            
            $conf = self::loadCongRedis();
            if (!self::checkConf($conf)) {
                $conf = self::loadConfFile();
            }
        }
        if (!self::checkConf($conf)) {
            // TODO load Config fail
            throw new \Exception('load Config fail');
        }

        if ($for_health_check) {
            return $conf;
        }
        $config = [];
        foreach (['base_service', 'data_service'] as $service_type) {
            # code...
            foreach ($conf[$service_type] as $key => $value) {
                # code...
                if ($value['status'] === 'active') {
                    if ($simple_info) {
                        $config[$value['name']] = $value['url'];
                        if ($service_type === 'base_service') {
                            $config[$key] = $value['url'];
                        }
                    } else {
                        $config[$value['name']] = [
                            'url' => $value['url'],
                            'route_list' => $value['route_list'],
                            'type' => $service_type === 'base_service' ? $key : 'data'
                        ];
                        
                        if ($service_type === 'base_service') {
                            $config[$key] = [
                                'url' => $value['url'],
                                'route_list' => $value['route_list'],
                                'type' => $service_type === 'base_service' ? $key : 'data'
                            ];
                        }

                    }
                }
            }
        }
        if (count($config) === 0) {
            // TODO 没有有效的服务器

        }
        return $config;
    }

    /**
     * @Descripttion: 将新的微服务config进行本地文件写入
     * @Author: LeungYin
     * @param Array $config
     * @return: void
     */
    static private function setConfFile($conf = null)
    {
        # code...
        file_put_contents(self::configPath(), $conf);
    }

    /**
     * @Descripttion: 将新的微服务config进行redis写入
     * @Author: LeungYin
     * @param Array $config
     * @return: void
     */
    static private function setConfredis($conf = null)
    {
        # code...
        Redis::set('micro_server_conf', $conf);
    }

    /**
     * @Descripttion: 将新的微服务config写入网关配置
     * @Author: LeungYin
     * @param Array $config
     * @return: Boolean
     */
    static public function setMircoserviceConfig($conf = null)
    {
        # code...
        if (is_null($conf)) {
            return false;
        }
        
        $conf = preg_replace_callback("#\\\u([0-9a-f]{1,4}+)#i", function ($matches) {
            return iconv('UCS-2BE', 'UTF-8', pack('H4', $matches[1]));
        }, $conf);
        try {
            //code...
            self::setConfFile($conf);
            self::setConfredis($conf);
            return true;
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }
    }

    // static public function compareConfig($old_conf = null, $new_conf = null)
    // {
    //     # code...
    //     // false for pick the old one and true for pick the new one
    //     if (isset($old_conf['force']) && $old_conf['force']) {
    //         $old_conf['force'] = false;
    //         return false;
    //     }
    //     if (isset($new_conf['force']) && $new_conf['force']) {
    //         $new_conf['force'] = false;
    //         return true;
    //     }
    //     if (is_null($new_conf) || !isset($new_conf['update_at'])) {
    //         return false;
    //     }
    //     if (is_null($old_conf) || !isset($old_conf['update_at'])) {
    //         return true;
    //     }
    //     if (\Carbon\Carbon::create($old_conf['update_at'])->diffInSeconds(\Carbon\Carbon::create($new_conf['update_at'])) >= 0) {
    //         return false;
    //     }
    //     //TODO 更多的校验配置规则
    //     return true;
    // }
}