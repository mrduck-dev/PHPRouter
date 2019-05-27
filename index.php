<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$router = new Router\Router();
/* $router->addRoute('/controller/action/','Controller\Controller','post');
$router->addRoute('/controller/post/','Controller\Controller','post');
$router->addRoute('/','Controller\Controller','index'); */
/* $route = $router->route('GET',['controller'=>'action']); */
$route = $router->get('/controller/action/',function(){
    echo "Route from index page";
});
/* $router->runDynamic();  */
//$router->getRoutes();
//$router->run();




