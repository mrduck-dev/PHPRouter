<?php

namespace Router;
use Request\Request;

class Router extends Request{

    private $params = [];
    private $routes = [];
    private $tempRoutes = [];
    private $message = "Page does not exists!!";


    public function __construct() {

        parent::__construct();
        
    }

    public function add(string $request,string $params) {
            preg_match('/^(GET|POST)\b(.+)/',$request,$matches);
            $basePath = trim($matches[2],' /');
            $urlPath = trim($this->getPath(),'/');
            $tokenExists =  preg_match_all('/{(\w+)}/',$basePath);

            if ($tokenExists > 0) {
                /* Get tokens if any and filter out empty values */
                $tokens = $this->findToken($basePath);
                $tokens = array_filter($tokens);

                    if (is_array($tokens) && !empty($tokens) ) {
                            /* Prepare paths for matching */
                            $basePath = trim($basePath);
                            $urlPath = substr($this->getPath(),0,strpos($basePath, '{'));
                            $basePath = substr($basePath, 0, strpos($basePath, '{'));     
                        }else {
                            return http_response_code(404);
                        }

            }//$tokenExist > 0

                        if ($this->getMethod() === $matches[1] && trim($urlPath,'/') === trim($basePath,'/')) {
                            $tokens = isset($tokens) ? $tokens : null ;
                            return $this->getControllerAction($params,$tokens);
                        }     
                        return http_response_code(404);
             
    }

    private function findToken($url){
        $tokenValues = [];
        $tokenName = [];
        $tokenPosition = [];
        $tokensFromUrl = [];
        $numberOfTokens = 0;
        preg_match_all('/{(\w+)}/',$url,$tokens);
       
        foreach ($tokens[0] as $token) {
            $numberOfTokens += 1;
            $tokenPosition[strpos($url,$token)] = strpos($url,$token);
            $tokenPosition[$token] = strlen($token);
            $tokensFromUrl[] = substr($this->getPath(),$tokenPosition[strpos($url,$token)]);
            
        }
       
       $preparedTokens = explode('/',$tokensFromUrl[0],$numberOfTokens);
       
       foreach ($preparedTokens as $value) {
            $tokenValues[] = trim($value,'/');

       }
       foreach ($tokens[1] as $name) {
           $tokenName[] = $name;
       }
            if (count($tokenName) === count($tokenValues)) {
                
                    $params = array_combine($tokenName,$tokenValues);
                    return $params;      
            }
            return;
       
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
            $urlPath = rtrim($this->getPath(),'/').'/';
            if ($this->getMethod() === $getRequest && array_key_exists($urlPath,$this->routes)) {
                
                   return $this->getControllerAction($this->routes[$urlPath]);
            }
            
        }else{
            return http_response_code(404);
        }
    }


    private function getControllerAction($param,$tokens = null){
        $controller = substr($param,0,strpos($param,'->'));
        $action = substr($param,strpos($param,'->'));
        $action = trim($action,'->');
        $controller = ucfirst($controller);
        if (class_exists($controller)) {
             $controller = new $controller();
             if (method_exists($controller,$action)) {
                return $controller->$action($tokens);
             }
        }   
    }


    public function get(string $path,callable $func) {
        if ($this->isGet() && $this->matchPath($this->getPath())) {
            if ($this->getPath() === $path) {
              return  call_user_func($func);
            }
            echo $this->message;
        }else{
        echo "Wrong request method";
        }
    }

    public function post(string $path,callable $func) {
        if ($this->isPost() && $this->matchPath($this->getPath()) ) {
            if ($this->getPath() === $path) {
              return  call_user_func($func);
            } 
           echo $this->message;
        }else{
        echo "Wrong request method";
        }
    }

    private function matchPath(string $matchItem) {
        if(preg_match("@(\/([a-z0-9+$ -].?)+)*\/?@",$matchItem)){
                return true;
        }
            return false;
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
                echo 'Page does not exists';
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

    private function getController(array $param) {
        $controller = ucfirst($param[1]);
        if (class_exists($controller. "\\" .$controller)) {
            $controller = $controller. "\\" .$controller;
            return new $controller();
        } else {
            throw new \Exception('Class does not exists');
        }
    }
}