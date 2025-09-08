<?php
namespace jeyroik\components\repositories;

use jeyroik\components\ApiEntity;
use jeyroik\interfaces\repositories\IRepository;
use jeyroik\interfaces\repositories\IRepositoryFactory;

class RepoApiCrudFactory implements IRepositoryFactory
{
    protected static array $repos = [];

    public static function get(string $entityName, string $dbClass = '', string $dbName = ''): IRepository
    {
        if (!isset(self::$repos[$entityName])) {
            
            $dbClass = empty($dbClass) ? DB__CLASS : $dbClass;
            self::$repos[$entityName] = new $dbClass(empty($dbName) ? DB__NAME : $dbName, $entityName, ApiEntity::class);
        }

        return self::$repos[$entityName];
    }
}
