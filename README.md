# PHPRouter
PHPRouter is a simple PHP router with different ways to handle routes. Work in progress.

```php
require_once __DIR__ . '/vendor/autoload.php';

$router = new Router\Router();

//Map your routes with add() method
$router->add('GET /controller/post/{id}','Controller\Controller->post');

//Handle routes with callback
$router->get('/controller/action/',function(){
    echo "Hello new router";
});

//Get maped routes from Routes.ini file
$router->getRoutes();
