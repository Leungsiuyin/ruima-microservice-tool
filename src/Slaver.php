<?php

namespace MicroserviceTool;

class Slaver {

    public $name;
    public $type;
    public $description;
    public $version;
    public $url;
    public $route;

    /**
     * Slaver constructor.
     */
    public function __construct($name, $type, $url, $description = '', $version = '', $route = [])
    {
      
        $this->$name = $name;
        $this->$type = $type;
        $this->$description = $description;
        $this->$version = $version;
        $this->$url = $url;
        $this->$route = $route;
    }

    public function getSlaverInfo()
    {
      # code...
      return [
        'name' => $this->name,
        'type' => $this->type,
        'description' => $this->description,
        'version' => $this->version,
        'url' => $this->url,
        'route' => $this->route,
      ];
    }

}