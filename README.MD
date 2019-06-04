# Ruima Microserver Framework base on Lumen

This is a microserver framework that make it easy to build a microserver API server.


## Dependencies
* PHP >= 7.1.3
* Lumen >= 5.8
* Guzzle >= 6.3

## Installation via Composer
```bash
$ composer require ruima/microservice-tool
```
Or if you prefer, edit `composer.json` manually:

```json
{
    "require": {
        "ruima/microservice-tool": "^0.0.2"
    }
}
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
    'auth' => Ruima\MicroserviceTool\FackAuth::class,
]);

$app->register(Ruima\MicroserviceTool\Provider\SlaverProvider::class);
```

#### For Gateway: (Comming soon)


### Installed routes
#### For Microservice:
Verb | Path | Controller | Action
--- | --- | --- | ---
GET | /health-check | \Ruima\MicroserviceTool\Controllers\SalverController | healthCheck
GET | /heart-beat | \Ruima\MicroserviceTool\Controllers\SalverController | heartBeat



#### For Gateway: (Comming soon)

### Installed methods
#### For Microservice:
```php
#use app('MicroserviceTool') to get the MicroserviceTool Object
app('MicroserviceTool')->{methods};

# return an Array for microservice infomations
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
#### For Gateway: (Comming soon)

