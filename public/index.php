<?php

use jeyroik\components\Router;
use jeyroik\interfaces\attributes\IHaveId;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestHandler;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

$beforeMiddleware = function (Request $request, RequestHandler $handler) use ($app) {
    // Example: Check for a specific header before proceeding
    $auth = $request->getHeaderLine('Authorization');
    if (!$auth) {
        // Short-circuit and return a response immediately
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write('Unauthorized');
        
        return $response->withStatus(401);
    }

    $r = new Router();
    if (!$r->isAllowed($auth)) {
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write('Access denied');
        
        return $response->withStatus(403);
    }

    // Proceed with the next middleware
    return $handler($request);
};

// Add error middleware
$app->addErrorMiddleware(
    displayErrorDetails:true, 
    logErrors: true, 
    logErrorDetails: true
);

// Add routes
$app->get('/{entity}/{id}', function (Request $request, Response $response, $args) use ($app) {
    $r = new Router();
    $item = $r->getRepo($args['entity'])->findOne([IHaveId::FIELD__ID => $args['id']]);
    $response->getBody()->write(json_encode($item->__toArray()));

    return $response;
});

$app->post('/{entity}/', function (Request $request, Response $response, $args) {
    
    $response->getBody()->write("args:" . json_encode($args));
    return $response;
});

$app->run();