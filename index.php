<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$router = new Router\Router();

$router->add('GET /controller/post/{id}/{bobi}','Controller\Controller->post');
/* $router->add('GET /controller/arr/{tr}','Controller\Controller->adduser');
$router->add('GET /controller/action/','Controller\Controller->index'); */

/* $route = $router->get('/controller/action/{id}',function($id){
    echo "Route from index page";
}); */
//$router->runDynamic(); 
$router->getRoutes();






