<?php
namespace jeyroik\components;

use jeyroik\components\repositories\THasRepository;
use jeyroik\interfaces\repositories\IHaveRepository;

class Router implements IHaveRepository
{
    use THasRepository;

    public function isAllowed(string $auth): bool
    {
        $allowed = include __DIR__ . '/../../resources/allowed.php';

        return isset($allowed[$auth]);
    }
}
