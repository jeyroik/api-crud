<?php
namespace jeyroik\components;

use jeyroik\components\exceptions\ExceptionNotFound;
use jeyroik\components\repositories\THasApiCrudRepo;
use jeyroik\interfaces\attributes\IHaveId;
use jeyroik\interfaces\entities\IApiEntity;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApiApp
{
    use THasApiCrudRepo;

    public function findOne(Response $response, string $entity, array $where): Response
    {
        $item = $this->getRepo($entity)->findOne($where);

        if (!$item) {
            throw new ExceptionNotFound();
        }

        $result = $item->__toArray();
        $this->vanishResponseData($result);

        $response->getBody()->write(json_encode($result));

        return $response;
    }

    public function findAll(Response $response, string $entity, array $where, int $offset = 0, int $limit = 0): Response
    {
        $items = $this->getRepo($entity)->findAll($where, offset: $offset, limit: $limit);

        $result = [];

        foreach ($items as $item) {
            $itemAsArray = $item->__toArray();
            $this->vanishResponseData($itemAsArray);
            $result[] = $itemAsArray;
        }

        $response->getBody()->write(json_encode($result));

        return $response;
    }

    public function updateOne(Response $response, string $entity, array $where, array $values): Response
    {
        $db = $this->getRepo($entity);
        $item = $db->findOne($where);

        if (!$item) {
            throw new ExceptionNotFound();
        }

        foreach ($values as $key => $value) {
            if (in_array($key, [IApiEntity::FIELD__USER, IApiEntity::FIELD__CREATED_AT])) {
                continue;
            }

            $item[$key] = $value;
        }
 
        $db->updateOne($item);

        $result = $item->__toArray();
        $this->vanishResponseData($result);

        $response->getBody()->write(json_encode($result));

        return $response;
    }

    public function deleteOne(Response $response, string $entity, array $where): Response
    {
        $db = $this->getRepo($entity);

        /**
         * @var IApiEntity $item
         */
        $item = $db->findOne($where);

        if (!$item) {
            throw new ExceptionNotFound();
        }
    
        $db->deleteOne($item);

        $response->getBody()->write(json_encode(['result' => 'success', 'details' => 'Item deleted']));

        return $response;
    }

    public function insertOne(Response $response, string $entity, array $data): Response
    {
        $result = $this->getRepo($entity)->insertOne($data);
        $result = $result->__toArray();

        $this->vanishResponseData($result);

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function isAllowed(string $auth, string $host): bool
    {
        $allowed = $this->getRepo('user')->findOne([IApiEntity::FIELD__USER => $auth]);

        if ($allowed) {
            if (in_array('*', $allowed['__domains__']) || in_array($host, $allowed['__domains__'])) {
                return true;
            }
        }

        return false;
    }

    public function initBaseUsers(): bool
    {
        $inited = $this->getRepo('user')->findAll();

        if (count($inited)) {
            return true;
        }

        $allowed = include __DIR__ . '/../../resources/allowed_tokens.php';

        foreach ($allowed as $user => $isOn) {
            $this->getRepo('user')->insertOne([
                IApiEntity::FIELD__USER => $user,
                '__domains__' => ['*'],
                '__allowed__' => true
            ]);
        }

        return true;
    }

    public function returnNotFound(string $entity, string $id, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'error' => 'item not found',
            'error_details' => [
                $entity => [
                    IHaveId::FIELD__ID => $id
                ]
            ]
        ]));

        return $response->withStatus(400);
    }

    public function returnError(string $entity, string $method, string $error, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'error' => $method . ': Error occured',
            'error_details' => [
                $entity => $error
            ]
        ]));

        return $response->withStatus(500);
    }

    public function getData(Request $request): array
    {
        return json_decode($request->getBody(), true) ?: [];
    }

    protected function vanishResponseData(array &$data): void
    {
        if (isset($data[IApiEntity::FIELD__USER])) {
            unset($data[IApiEntity::FIELD__USER]);
        }

        if (isset($data[IApiEntity::FIELD__SYSTEM_ID])) {
            unset($data[IApiEntity::FIELD__SYSTEM_ID]);
        }     
    }
}
