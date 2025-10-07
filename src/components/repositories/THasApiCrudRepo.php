<?php
namespace jeyroik\components\repositories;

use jeyroik\interfaces\repositories\IRepository;

trait THasApiCrudRepo
{
    public function getRepo(string $entityName): IRepository
    {
        return RepoApiCrudFactory::get($entityName, DB__CLASS, DB__NAME);
    }
}