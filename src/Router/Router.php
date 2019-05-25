<?php

namespace Router;

use Request\Request;
use Controller\Controller as Controller;

class Router extends Request{

    private $params = [];
    private $routes = [];
    private $class;
    private $func;
    private $id;

    public function __construct() {

        parent::__construct();
        
    }

    public function addRoute(string $route,string $class,string $func) {
                 
        /* if (strpos($route,':id') !== false) {
                $route = substr($route, 0, strpos($route, ':')); //ovde hvatamo parametar funkcije addRoute() pre id 
                $this->id = substr($this->getPath(),strlen($route)); //here will be the code to catch soken from url
                $pathBesforeToken = substr($this->getPath(), 0, strlen($route)); //ovde hvatamo URL  pre id 
                        
        } */
        $route = rtrim($route,"/");
        $this->routes[] = $route;
        $this->class = $class;
        $this->func = $func;
   
       /*  $urlPath = explode('/',$this->getPath());
        $urlPath = array_filter($urlPath);  */ 
        
    }

    public function run() {
       $path = rtrim($this->getPath(),"/");
        if (in_array($path,$this->routes)) {
            $routeClass = new $this->class;
            return  $routeClass->{$this->func}();
             
        }
    }

    public function route(string $request,array $path) {

    }

    public function get(string $path,callable $func){

    }

    public function post(string $path,callable $func) {

    }

    public function runDynamic() {

        if ($this->isGet()) {
            $this->handleDynamicRoute($this->getPath());
        } else {
            new \Exception("Something is wrong with path");
        }
    }

    private function handleDynamicRoute($path) {
        $controller = explode("/", $path);
        foreach ($controller as $key => $value) {
            $this->params[] = addslashes($value);
        }
            $param = $this->params;
            if (empty($param)) {
                $controller =  new Controller();
                    return $controller->index();
            } else {
        $controller = $this->getController($param);
        $action = $this->getAction($param);
            return $controller->$action();
        } 
    }

    private function getAction(array $param) : string {
        $controller = $this->getController($param);
        if (isset($param[2])) {
            $action = $param[2];
            if (method_exists($controller,$action)) {  
                return $action;
            } else {
                return 'index';
            }
        } else {
            return 'index';
        }
        
    }

    private function getController(array $param) : object{
        $controller = $param[1];
        if (class_exists($controller)) {
            $controller = new $controller. "\\" .$controller;
            return $controller;
        } else {
            return new Controller();
        }
    }
}