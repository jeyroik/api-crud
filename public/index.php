<?php

use Dotenv\Parser\Entry;
use jeyroik\components\Entity;
use jeyroik\components\repositories\RepositoryMongo;
use jeyroik\components\Router;
use jeyroik\interfaces\attributes\IHaveId;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestHandler;

require __DIR__ . '/../vendor/autoload.php';

defined('MONGO__DSN') or define('MONGO__DSN', getenv('MONGO__DSN') ?: 'mongodb://localhost');
defined('DB__CLASS') or define('DB__CLASS', getenv('DB__CLASS') ?: RepositoryMongo::class);
defined('DB__NAME') or define('DB__NAME', getenv('DB__NAME') ?: 'crud_entity');
defined('REPOSITORY__PLUGINS_FILE') or define(
    'REPOSITORY__PLUGINS_FILE', 
    getenv('REPOSITORY__PLUGINS_FILE') ?: __DIR__ . '/../resources/plugins.php'
);

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
    $item = $r->getRepo(Entity::class)->findOne([
        IHaveId::FIELD__ID => $args['id'],
        Entity::FIELD__ENTITY => $args['entity'],
        Entity::FIELD__USER => $request->getHeaderLine('Authorization')
    ]);

    if (!$item) {
        $response->getBody()->write(json_encode([
            'error' => 'item not found',
            'error_details' => [
                IHaveId::FIELD__ID => $args['id'],
                Entity::FIELD__ENTITY => $args['entity'],
                Entity::FIELD__USER => $request->getHeaderLine('Authorization')
            ]
        ]));

        return $response;    
    }

    $result = $item->__toArray();
    unset($result[Entity::FIELD__ENTITY]);

    $response->getBody()->write(json_encode($result));

    return $response;
});

$app->post('/{entity}/', function (Request $request, Response $response, $args) {
    $json = json_decode($request->getBody(), true);
    $json[Entity::FIELD__ENTITY] = $args['entity'];
    $json[Entity::FIELD__USER] = $request->getHeaderLine('Authorization');

    $r = new Router();
    $result = $r->getRepo(Entity::class, RepositoryMongo::class, 'crud')->insertOne($json);
    $result = $result->__toArray();
    unset($result[Entity::FIELD__ENTITY]);

    $response->getBody()->write(json_encode($result));
    return $response;
});

$app->run();