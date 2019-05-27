<?php

namespace Router;

use Request\Request;
use Controller\Controller as Controller;

class Router extends Request{

    private $params = [];
    private $routes = [];
    private $tempRoutes = [];
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

    public function getRoutes(){
        $filename = __DIR__.'/Routes.ini';
        if (file_exists($filename)) {
            $routes = parse_ini_file($filename,true);
            foreach ($routes as $request => $path) {
                $this->tempRoutes[$request] = $path;
            }
            foreach ($this->tempRoutes['routes'] as $key => $value) {
                $getPath = trim(substr($key, strpos($key, ' ')));
                $this->routes[$getPath] = $value;
                $getRequest = substr($key, 0, strpos($key, ' '));
                
            }
            if ($this->getMethod() === $getRequest) {
                $urlPath = rtrim($this->getPath(),'/').'/';
                if (array_key_exists($urlPath,$this->routes)) {
                    $class = substr($this->routes[$urlPath],0,strpos($this->routes[$urlPath],'->'));
                    $action = substr($this->routes[$urlPath],strpos($this->routes[$urlPath],'->'));
                    $action = trim($action,'->');
                    $class = new $class();
                    return $class->$action();
                }
            }
           // print_r($this->routes);
            
        }else{
            echo 'File does not exists';
        }
    }

    public function route(string $request,array $path) {
        if ($this->getMethod() === $request) {
            $urlPath = explode('/',$this->getPath());
            
            foreach ($urlPath as $key => $value) {
                $this->routes[$key] = $value;
            }
            print_r($this->routes);
        } else {
            echo "Wrong request";
        }
        
    }

    public function get(string $path,callable $func){
        if ($this->isGet()) {
            if ($this->getPath() === $path) {
                call_user_func($func);
            } else {
                echo "Page thoes not exists";
            }
        }
    }

    public function post(string $path,callable $func) {
        if ($this->isPost()) {
            if ($this->getPath() === $path) {
                call_user_func($func);
            } else {
                echo "Page thoes not exists";
            }
        }
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