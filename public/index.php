<?php

use jeyroik\components\repositories\RepositoryMongo;
use jeyroik\components\ApiApp;
use jeyroik\components\exceptions\ExceptionNotFound;
use jeyroik\interfaces\entities\IApiEntity;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

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
$apiApp = new ApiApp();

$authMiddleware = function (Request $request, RequestHandler $handler) use ($app, $apiApp) {
    // Example: Check for a specific header before proceeding
    $auth = $request->getHeaderLine('Authorization');
    if (!$auth) {
        // Short-circuit and return a response immediately
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write('Unauthorized');
        
        return $response->withStatus(401);
    }

    if (!$apiApp->isAllowed($auth)) {
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write('Access denied');
        
        return $response->withStatus(403);
    }

    // Proceed with the next middleware
    return $handler->handle($request);
};

$app->add($authMiddleware);

// Add error middleware
$app->addErrorMiddleware(
    displayErrorDetails:true, 
    logErrors: true, 
    logErrorDetails: true
);

// Add routes
$app->get('/{entity}/{id}', function (Request $request, Response $response, $args) use ($app, $apiApp) {
    try {
        return $apiApp->findOne($response, $args['entity'], [
            IApiEntity::FIELD__ID => $args['id'],
            IApiEntity::FIELD__USER => $request->getHeaderLine('Authorization')    
        ]);
    } catch (ExceptionNotFound $e) {
        return $apiApp->returnNotFound($args['entity'], $args['id'], $response);
    } catch (\Exception $e) {
        return $apiApp->returnError($args['entity'], 'Get entity', $e->getMessage(), $response);
    }
});

$app->put('/{entity}/{id}', function (Request $request, Response $response, $args) use ($app, $apiApp) {
    try {
        return $apiApp->updateOne($response, $args['entity'], [
            IApiEntity::FIELD__ID => $args['id'],
            IApiEntity::FIELD__USER => $request->getHeaderLine('Authorization')
        ], $apiApp->getData($request));
    } catch (ExceptionNotFound $e) {
        return $apiApp->returnNotFound($args['entity'], $args['id'], $response);
    } catch (\Exception $e) {
        return $apiApp->returnError($args['entity'], 'Update entity', $e->getMessage(), $response);
    }
});

$app->delete('/{entity}/{id}', function (Request $request, Response $response, $args) use ($app, $apiApp) {
    try {
        return $apiApp->deleteOne($response, $args['entity'], [
            IApiEntity::FIELD__ID => $args['id'],
            IApiEntity::FIELD__USER => $request->getHeaderLine('Authorization')
        ]);
    } catch (ExceptionNotFound $e) {
        return $apiApp->returnNotFound($args['entity'], $args['id'], $response);
    } catch (\Exception $e) {
        return $apiApp->returnError($args['entity'], 'Delete item', $e->getMessage(), $response);
    }
});

$app->post('/{entity}/', function (Request $request, Response $response, $args) use ($apiApp) {
    $json = $apiApp->getData($request);
    $json[IApiEntity::FIELD__USER] = $request->getHeaderLine('Authorization');

    return $apiApp->insertOne($response, $args['entity'], $json);
});

$app->run();
