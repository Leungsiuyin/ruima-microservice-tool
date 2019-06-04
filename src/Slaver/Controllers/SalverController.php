<?php

namespace Ruima\MicroserviceTool\Controllers;
// use Psr\Http\Message\ServerRequestInterface;

class SalverController {
    
    public function healthCheck()
    {
        # code...
        return response()->json(app('MicroserviceTool')->getSlaverInfo());
    }
}