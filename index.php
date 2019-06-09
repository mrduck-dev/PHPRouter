<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$router = new Router\Router();

/* $router->add('GET /controller/action/{momo}','Controller\Controller->post');
$router->add('GET /controller/arr/{bobo}','Controller\Controller->adduser');
$router->add('GET /','Controller\Controller->index');


/* $router->get('/{var}',function($id){
    var_dump($id);
}); */

$router->loadRoutes();






