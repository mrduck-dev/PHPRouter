<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$router = new Router\Router();

$router->add('GET /{controller}/{post}','Controller\Controller->post');
$router->add('GET /controller/arr/{bobo}','Controller\Controller->adduser');
$router->add('GET /controller/action/','Controller\Controller->index');

/* $router->get('controller/action/{mimi}',function($id){
    var_dump($id);
}); */

//$router->getRoutes();






