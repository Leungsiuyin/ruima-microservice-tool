<?php

namespace Ruima\MicroserviceTool\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client as HttpClient;
use Laravel\Lumen\Routing\Controller as BaseController;
use Ruima\MicroserviceTool\Console\Commands\HealthCheck;
use Ruima\MicroserviceTool\Console\Commands\HeartBeatCheck;
use Ruima\MicroserviceTool\Services\MicroAuthConfigService;
use Ruima\MicroserviceTool\Services\MicroserverConfigService;

// use Psr\Http\Message\ServerRequestInterface;

class MasterController extends BaseController {

    /**
     * @Descripttion: 网关路由分发控制器
     * @Author: LeungYin
     * @param Request $request
     * @return: Http Response
     */
    public function distribute(Request $request)
    {
        // 从请求截取需要信息
        $service_name = substr($request->path(),0,strpos($request->path(), '/'));
        $service_path = substr($request->path(),strpos($request->path(),'/', 1));
        $service_path_with_query = substr($request->getRequestUri(),strpos($request->getRequestUri(),'/', 1));
        $service_config = MicroserverConfigService::loadMircoserviceConfig();
        if ($service_name === '') {
            $service_name = $service_path;
            $service_path = '/';
            $service_path_with_query = substr_replace($request->getRequestUri(), '', 1, strlen($service_name));
            // '/'.substr($request->getRequestUri(),strpos($request->getRequestUri(), '?', 1));
        }
        // dd([
        //     'path' => $request->path(),
        //     'getRequestUri' => $request->getRequestUri(),
        //     'service_name' =>$service_name,
        //     'service_path' =>$service_path,
        //     'service_path_with_query' => $service_path_with_query
        // ]);
        $service_url = isset($service_config[$service_name]) && isset($service_config[$service_name]['url']) ? $service_config[$service_name]['url'] : false;
        if (!$service_url) {
            return response('service not found!', 404);
        }
        
        // 网关进行路由存在性判断
        $target = array_filter($service_config[$service_name]['route_list'], function ($el) use($request, $service_path) {
            return $el['method'] === $request->method() && ( $el['params'] == 0 ? $service_path === $el['reg'] : (boolean) preg_match("#^".rawurldecode($el['reg'])."$#", $service_path));
        });
        if (count($target) === 0) {
            //TODO return 404
            return response('page not found!', 404);
        }

        $http_config = $this->handleHttpConfig($request, $service_config);

        // 网关进行权鉴判断
        if ($target[array_keys($target)[0]]['auth']) {
            // dd($target[array_keys($target)[0]]);
            $auth_info = $this->checkToken($request);
            if (is_null($auth_info)) {
                return response('Unauthorized.', 401);
            }
            // dd($service_config);
            $http_config['query']['user'] = json_encode($auth_info);
        }
        

        // $target = $target[array_keys($target)[0]];
        // $service_path = preg_replace("#^".rawurldecode($target['reg'])."$#", '/\w+', $el['uri'], -1, $el['params']);;
        // dd($target);



        $http = new HttpClient();

        $response = $http->request($request->method(), $service_url.$service_path_with_query, $http_config);
        $result = $response->getBody();
        return response($result, $response->getStatusCode());
    }

    /**
     * @Descripttion: 检查请求中是否有token，token是否有效
     * @Author: LeungYin
     * @param Request $request
     * @return: 
     */
    private function checkToken(Request $request)
    {
        # code...
        $token = $request->header('Authorization');

        if (is_null($token)) 
        {
            return null;
        }
        try {
            //code...
            $info = MicroAuthConfigService::getAuthInfo($token);
        } catch (\Throwable $th) {
            // throw $th;
            $info = null;
        }
        return $info;
    }

    /**
     * @Descripttion: 处理分发请求的Guzzle config数组
     * @Author: LeungYin
     * @param Request $request, $service_config = null
     * @return: Array
     */
    private function handleHttpConfig(Request $request, $service_config = null)
    {
        # code...
        $headers = $request->headers->all();
        unset($headers['host']);
        unset($headers['content-length']);
        unset($headers['content-type']);
        $http_config = [
            'headers' => $headers,
            'http_errors' => false
        ];

        $query = [];
        foreach ($request->query() as $key => $value) {
            # code...
            if ($key !== "_url") {
                $query[$key] = $value;
            }
        }
        $http_config["query"] = $query;

        $server_list = [];
        foreach ($service_config as $key => &$value) {
            if ($value['type'] !== 'data') {
                $server_list[$value['type']] = $value['url'];
            } else {
                $server_list[$key] = $value['url'];
            }

        }
        // $server_list['gateway']
        $server_list_json = json_encode($server_list);

        $multipart = [];
        if (array_search($request->method(), ['POST', 'PUT', 'PATCH']) !== false) {
            foreach ($request->all() as $key => $value) {
                # code...
                $content = [
                    "name" => $key
                ];
                if (!is_string($value)) {
                //     # code...
                    $content["filename"] = $value->getClientOriginalName();
                    $content["contents"] = fopen($value, 'r');

                } else {
                    $content["contents"] = $value;
                }
                if ($key !== "_url") {
                    array_push($multipart, $content);
                }
            }
            // if (!is_null($server_list)) {
            //     array_push($multipart, [
            //         "name" => "service_config",
            //         "contents" => $server_list_json
            //     ]);
            // }
            $http_config['multipart'] = $multipart;
        } else {
            // $http_config["query"]["service_config"] = $server_list_json;
        }
        // dd($server_list);


        // $http_config['form_params'] = $request->all();
        // unset($http_config['form_params']['_url']);
        return $http_config;
    }

    /**
     * @Descripttion: 通过请求触发health check，用于测试health check功能
     * @Author: LeungYin
     * @param Null
     * @return: Http Response
     */
    public function healthCheck()
    {
        $conf = HealthCheck::handle();
        return response()->json($conf);
    }
    
    public function heartBeatCheck()
    {
        $conf = HeartBeatCheck::handle();
        return response()->json($conf);
    }

    /**
     * @Descripttion: 微服务上线时主动向网关发起注册
     * @Author: LeungYin
     * @param Request $request
     * @return: Http Response
     */
    public function registerMicroserver(Request $request)
    {
        # code...
        // dd($request);
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
            'url' => 'required',
        ],[
            // 'name.required' => '缺少登陆用户名',
        ]);
        $service_config = MicroserverConfigService::loadMircoserviceConfig(true);

        $hadConf = false;
        $type_arr = [];
        if ($request->input('type') === 'data') {
            array_push($type_arr, 'data_service');
        } else {
            array_push($type_arr, 'base_service');
        }
        foreach ($type_arr as $service_type) {
            # code...
            foreach ($service_config[$service_type] as &$value) {
                # code...
                // dd($value);
                try {
                    //code...
                    if (isset($value['name']) && $value['name'] === $request->input('name')) {
                        $hadConf = true;
                        $value['type'] = $request->input('type');
                        $value['url'] = $request->input('url');
                        $value['status'] = 'active';
                        $value['description'] = $request->input('description') ?? $value['description'];
                        // $value['version'] = $request->input('version') ?? $value['version'];
                        $value['route_list'] = $request->input('route');
                        $value['update_at'] = Carbon::now()->toDateTimeString();
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    // dd($value);
                }
            }
        }
        if (!$hadConf) {
            $field = [
                "name" => $request->input('name'),
                "url" => $request->input('url'),
                'status' => 'active',
                "description" => $request->input('description'),
                // "version" => $request->input('version'),
                'route_list' => $request->input('route'),
                'update_at' => Carbon::now()->toDateTimeString()
            ];
            if ($request->input('type') === 'data') {
                array_push($service_config['data_service'], $field);
            } else {
                $service_config['base_service'][$request->input('type')] = $field;
            }
        }
        // $service_config['update_at'] = Carbon::now()->toDateTimeString();
        try {
            //code...
            MicroserverConfigService::setMircoserviceConfig(json_encode($service_config));
        } catch (\Throwable $th) {
            //throw $th;
            // TODO 写入配置文件失败
        }
        // return response()->json($service_config);
        return response()->json(MicroserverConfigService::loadMircoserviceConfig(false, true));

    }

    /**
     * @Descripttion: 微服务发生需要刷新auth信息的请求，删除redis中缓存的auth信息，下次请求从auth服务中重新获取auth信息
     * @Author: LeungYin
     * @param Request $request
     * @return: 
     */
    public function distroyAuth(Request $request)
    {
        # code...
        MicroAuthConfigService::distroyAuthToken($request->input('sort_token'));
        return response('', 204);
    }
}