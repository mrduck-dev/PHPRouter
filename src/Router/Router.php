<?php

namespace Router;
use Request\Request;

class Router extends Request{

    private $routes = [];
    private $tempRoutes = [];


    public function __construct() {

        parent::__construct();
        
    }

    public function add(string $request,string $params) {

            preg_match('/^(GET|POST)\b(.+)/',$request,$matches);

            if ($this->getMethod() === $matches[1]) {
                return $this->prepareRoute($matches[2],$params);
             
            }else{
                http_response_code(404);
                require_once '404.html';
            }
                 
             
    }




    public function loadRoutes(){

        $filename = __DIR__.'/Routes.ini';
        $urlPath = '/'.trim($this->getPath(),'/').'/';

        if (file_exists($filename)) {
            $routes = parse_ini_file($filename,true);

            foreach ($routes as $request => $path) {
                $this->tempRoutes[$request] = $path;
            }

            foreach ($this->tempRoutes['routes'] as $key => $value) {

                $basePath = trim(substr($key, strpos($key, ' ')));
                $tokenExists =  preg_match_all('/@(\w+)/',$basePath);

                if ($tokenExists > 0) {
                        /* Get tokens if any and filter out empty values */
                        $tokens = $this->findToken($basePath);

                        if (is_array($tokens) && !empty($tokens) ) {
                                /* Prepare paths for matching */
                                $basePath = trim($basePath);
                                $urlPath = substr($this->getPath(),0,strpos($basePath, '@'));
                                $basePath = substr($basePath, 0, strpos($basePath, '@'));     
                            }
                }

                $this->routes[$basePath] = $value;   
                $getRequest = substr($key, 0, strpos($key, ' '));
                
            }
            
            if ($this->getMethod() === $getRequest && array_key_exists($urlPath,$this->routes)) {
                    $tokens = isset($tokens) ? $tokens : null ;
                   return $this->getControllerAction($this->routes[$urlPath],$tokens);
            }
            
        }else{
            return http_response_code(404);
        }
    }


    public function get(string $path,callable $func) {

            if ($this->isGet() && $this->matchPath($this->getPath())) {
                    
            return $this->prepareRoute($path,$func);

            }else{
            echo "Wrong request method";
            }
    }


    public function post(string $path,callable $func) {

            if ($this->isPost() && $this->matchPath($this->getPath()) ) {

            return $this->prepareRoute($path,$func);
                
            }else{
            echo "Wrong request method";
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



    private function prepareRoute(string $path,$param){

        $basePath = '/'.trim($path,' /').'/';
        $urlPath = '/'.trim($this->getPath(),'/').'/';
        $tokenExists =  preg_match_all('/{(\w+)}/',$basePath);

            if ($tokenExists > 0) {
                /* Get tokens if any and filter out empty values */
                $tokens = $this->findToken($basePath);
                    if (is_array($tokens) && !empty($tokens) ) {
                            /* Prepare paths for matching */
                            $basePath = trim($basePath);
                            $urlPath = substr($this->getPath(),0,strpos($basePath, '{'));
                            $basePath = substr($basePath, 0, strpos($basePath, '{'));     
                        }else {
                            return http_response_code(404);
                        }

            }//$tokenExist > 0

        if ($urlPath === $basePath) {
            $tokens = isset($tokens) ? $tokens : null ;
            if (is_callable($param)) {
                return  call_user_func($param,$tokens);
            } else {
                return $this->getControllerAction($param,$tokens);
            }
            
          
        }
       
    }


    private function findToken($url){
        $tokenValues = [];
        $tokenName = [];
        $tokenPosition = [];
        $tokensFromUrl = [];
        $numberOfTokens = 0;
        $tokenTypeExists = preg_match('/{(\w+)}/',$url);
        $tokenAtExists = preg_match('/@(\w+)/',$url);
        
            if ($tokenTypeExists > 0) {
                preg_match_all('/{(\w+)}/',$url,$tokens);
            }elseif ($tokenAtExists > 0) {
                preg_match_all('/@(\w+)/',$url,$tokens);
            }
        
                foreach ($tokens as $token) {
                        foreach ($token as $key) {
                            $numberOfTokens += 1;
                            $tokenPosition[strpos($url,$key)] = strpos($url,$key);
                            $tokenPosition[$key] = strlen($key);
                            $tokensFromUrl[] = substr($this->getPath(),$tokenPosition[strpos($url,$key)]);
                        }
                }
                    $preparedTokens = explode('/',$tokensFromUrl[0],$numberOfTokens);
                    foreach ($preparedTokens as $value) {
                            $tokenValues[] = trim($value,'/');
                    }

        $tokenValues = array_filter($tokenValues);
        foreach ($tokens[1] as $name) {
            $tokenName[] = $name;
        }

            if (count($tokenName) === count($tokenValues)) {
                    $params = array_combine($tokenName,$tokenValues);
                    return $params;      
            }
            return;
   
    }


    private function matchPath(string $matchItem) : bool {
        if(preg_match("@(\/([a-z0-9+$ -].?)+)*\/?@",$matchItem)){
                return true;
        }
            return false;
    }

}