<?php

namespace Ruima\MicroserviceTool\Controllers;

use Ruima\MicroserviceTool\Console\Commands\HeartBeatCheck;

// use Psr\Http\Message\ServerRequestInterface;

class MasterController {
    
    public function healthCheck()
    {
        # code...
        return response()->json('success');
    }

    public function heartBeatCheck()
    {
        # code...
        return response()->json(HeartBeatCheck::handle());
    }
}