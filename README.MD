# Ruima Microserver Framework base on Lumen

## Description
This is a microserver framework that make it easy to build a microserver API server.
The gateway will cache the user auth info (base info, roles info, premissions info) in redis.

## What did this package do?
* Automatically distribute the requset to target microservice
* Use redis to cache the user auth info in gateway
* Authentication in gateway
* Api permission guard in microservice middleware

## Dependencies
* PHP >= 7.1.3
* Lumen >= 5.8
* Guzzle >= 6.3
* illuminate/redis >= 5.8
* predis/predis >= 1.1

## Installation via Composer
```bash
$ composer require ruima/microservice-tool
```
Or if you prefer, edit `composer.json` manually:

```json
{
    "require": {
        "ruima/microservice-tool": "^0.1.1"
    }
}
```

### Starting The Scheduler
You need to add the following Cron entry to your server.
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Modify the service environment (```.env``` file)
#### For Microservice:
```
# (Require) the url of this service
APP_URL=http://your.app.url

# (Require) the url of gateway
MICROSERVICE_GATEWAY_URL = http://your.gateway.url

# (Require) the name of this microservice
MICROSERVICE_NAME = micro-data

# (Require) the type of this microservice 
# auth (basic microservice) | data (normal microservice)
MICROSERVICE_TYPE = data

# (Not Require) the description of this microservice
MICROSERVICE_DESCRIPTION = 'Data microservice Demo'

# (Not Require) the version of this microservice, default return lumen's version
MICROSERVICE_VERSION = ''

# (Not Require) the administrator's code when using permission middleware (the administrator can access all controller), default ADMIN
MICROSERVICE_ADMIN_CODE = ADMIN
```
#### For Auth Microservice:
You need to add APP_KEY
```
APP_KEY=The only supported ciphers are AES-128-CBC or AES-256-CBC
```

#### For Gateway:
```
# The gateway use redis to cache the user auth info , you need to set the redis config
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password

# (Not Require) custom the microservices config list dir 
MICROSERVICE_CONFIG_DIR = /absolute/path/to/your/config/dir

# (Not Require) print how much time spend when gateway distribute request.
MICROSERVICE_GATEWAY_TIME_LOG = true
```

### Modify the bootstrap flow (```bootstrap/app.php``` file)
#### For Microservice:
```php
$app->singleton(
    'MicroserviceTool', function ($app) {
        return new Ruima\MicroserviceTool\Slaver($app);
    }
);

$app->routeMiddleware([
    'auth' => Ruima\MicroserviceTool\Middleware\FackAuth::class,
    'permission' => Ruima\MicroserviceTool\Middleware\Permission::class,
]);

$app->register(Ruima\MicroserviceTool\Provider\SlaverProvider::class);
```

#### For Gateway: 
``` php
$app->register(Illuminate\Redis\RedisServiceProvider::class);

$app->register(Ruima\MicroserviceTool\Provider\MasterProvider::class);
```

### Installed routes
#### For Microservice:
Verb | Path | Controller | Action
--- | --- | --- | ---
GET | /health-check | \Ruima\MicroserviceTool\Controllers\SalverController | healthCheck
GET | /heart-beat | \Ruima\MicroserviceTool\Controllers\SalverController | heartBeat

#### For Gateway: 
Verb | Path | Controller | Action
--- | --- | --- | ---
GET | /health-check | \Ruima\MicroserviceTool\Controllers\MasterController | healthCheck
GET | /heart-beat-check | \Ruima\MicroserviceTool\Controllers\MasterController | heartBeatCheck
GET | /registe-microserver | \Ruima\MicroserviceTool\Controllers\MasterController | healthCheck
POST | /distroy-auth | \Ruima\MicroserviceTool\Controllers\MasterController | registerMicroserver
DELETE | /health-check | \Ruima\MicroserviceTool\Controllers\MasterController | distroyAuthToken
GET | {path:.*} | \Ruima\MicroserviceTool\Controllers\MasterController | distribute
POST | {path:.*} | \Ruima\MicroserviceTool\Controllers\MasterController | distribute
PUT | {path:.*} | \Ruima\MicroserviceTool\Controllers\MasterController | distribute
PATCH | {path:.*} | \Ruima\MicroserviceTool\Controllers\MasterController | distribute
DELETE | {path:.*} | \Ruima\MicroserviceTool\Controllers\MasterController | distribute
OPTIONS | {path:.*} | \Ruima\MicroserviceTool\Controllers\MasterController | distribute

### Installed schedules & commandsdules
#### For Microservice:
Cycle | Command | Description
--- | --- | --- 
5MIN | microserver:heart-beat | send the microservice info to gateway

#### For Gateway: 
Cycle | Command | Description
--- | --- | --- 
5MIN | microserver:heart-beat-check | check the info in gateway

### Installed methods
#### For Microservice:
```php
#use app('MicroserviceTool') to get the MicroserviceTool Object
app('MicroserviceTool')->{methods};

# return an Array for microservice infos
getSlaverInfo()

# send the request to tell gateway destory the user cache in gateway
destoryAuth() 

# return a String for target microservice url
getServerUrl(String $service_name) 

# return a http body which the target microserver callback
get(String $service_name, String $url = '/the/route/which/defind/in/web.php/or/api.php', Array $guzzle_config = []) 
post(String $service_name, String $url, Array $guzzle_config) 
delete(String $service_name, String $url, Array $guzzle_config) 
put(String $service_name, String $url, Array $guzzle_config) 
```
#### For Gateway: 
```
 (Comming soon)
```

### Destribute request to target microservice
Follow this rule:
```
http://gateway.url/microservice.name/microservice.router.url
```

.e.g
There is a microservice which the `MICROSERVICE_NAME` in `.env` is "micro-auth", and it have a route `GET/auth-info`.When send a request use the following url, gateway will handle this automatically.
```
http://loacal.gateway.com/micro-auth/auth-info
```

### Use Authorization In Microservice
#### In Auth microservice
You need to add a route "GET/auth-info", return a json format user auth info.
when other microservice need the auth info, gate way will seed a http request with token in GET method to "/auth-info", then the gateway wiil use the last 64 bits of the token as the key to cache the response in redis.
When auth info changed, you need to send a request "POST/distroy-auth" with body { short_token (the last 64 bits of the token) } to gateway. if short_token = "all", the gateway will clear all cache.

In api router:
```php
$router->get('auth-info', 'AuthenticateController@getAuthInfo');
```
The suggest user auth info format:
```json
{
    "id": 1,
    "name": "admin",
    "username": "admin",
    "token": "9hkIE4iVo_60QbbB8t1Ho-jkde7gzLZDubcUifRrd4YINZJxnq9F3-c6nJFS6EAk",
    "roles": [
      "admin",
    ],
    "permissons": [
      "ADMIN",
    ]
}
```

#### In Other microservice
##### Use middleware auth in your route
In Route
```php
$router->get('/', [ 'middleware' => 'auth' , function () use ($router) {
    return $router->app->version();
}]);
```
In Controller
```php
public function index (Request $request) {
    $AUTH = $request->auth;
}
```

##### Use middleware permission in your route
When using permission middle, it will use auth middleware automatically.
and check the `\Array $request->auth['permissions']` have the right permissin or not.
In the case of multiple permissions, as long as user have one permission, it will pass the permission check.
```php

$router->get('/', [ 'middleware' => 'permission:PA ADMIN RE' , function () use ($router) {
    return $router->app->version();
}]);
```

