<?php

namespace Request;

class Request {

     const GET = 'GET';
     const POST = 'POST';

    private $method;
    private $host;
    private $path;

    public function __construct() {
        
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->host = $_SERVER['HTTP_HOST'];
        $this->path = $_SERVER['REQUEST_URI'];
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function getHost() : string {
        return $this->host;
    }

    public function getPath() : string {
        return $this->path;
    }

    public function getUrl() : string {
        return $this->host . $this->path;
    }

    public function isGet() : bool {
        return $this->method === self::GET;
    }

    public function isPost() : bool {
        return $this->method === self::POST;
    }

}