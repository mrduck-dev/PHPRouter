<?php

namespace Router;
use Request\Request;

class Router extends Request{

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

    private function token($basePath,$urlPath){
            /* Get tokens if any and filter out empty values */
            $tokens = $this->findToken($basePath);

                if (is_array($tokens) && !empty($tokens) ) {
                        /* Prepare paths for matching */
                        $basePath = trim($basePath);
                        $urlPath = substr($this->getPath(),0,strpos($basePath, '{'));
                        $basePath = substr($basePath, 0, strpos($basePath, '{'));   
                         return $tokens;  
                    }else {
                        return http_response_code(404);
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

    

    public function getRoutes(){
        $filename = __DIR__.'/Routes.ini';
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
                            }else {
                                return http_response_code(404);
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
                
            $this->getRoute($path,$func);
        }else{
        echo "Wrong request method";
        }
    }

    public function post(string $path,callable $func) {
        if ($this->isPost() && $this->matchPath($this->getPath()) ) {
            $this->getRoute($path,$func);
            
        }else{
        echo "Wrong request method";
        }
    }

    private function getRoute(string $path,callable $func){
        $basePath = trim($path,' /');
        $urlPath = trim($this->getPath(),'/');
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

        if (trim($urlPath,'/') === trim($basePath,'/')) {
            
          return  call_user_func($func,$tokens);
        }
       echo $this->message;
    }

    private function matchPath(string $matchItem) : bool {
        if(preg_match("@(\/([a-z0-9+$ -].?)+)*\/?@",$matchItem)){
                return true;
        }
            return false;
    }

}