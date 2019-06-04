<?php

namespace Ruima\MicroserviceTool\Controllers;

use Ruima\MicroserviceTool\Console\Commands\HeartBeat;

// use Psr\Http\Message\ServerRequestInterface;

class SalverController {
    
    public function healthCheck()
    {
        # code...
        return response()->json(app('MicroserviceTool')->getSlaverInfo());
    }

    public function heartBeat()
    {
        # code...
        return response()->json(HeartBeat::handle());
    }
}