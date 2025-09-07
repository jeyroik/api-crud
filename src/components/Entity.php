<?php
namespace jeyroik\components;

use jeyroik\components\attributes\THasCreatedAt;
use jeyroik\components\attributes\THasIdString;
use jeyroik\components\attributes\THasUpdatedAt;
use jeyroik\interfaces\attributes\IHaveCreatedAt;
use jeyroik\interfaces\attributes\IHaveIdString;
use jeyroik\interfaces\attributes\IHaveUpdatedAt;

class Entity implements IHaveIdString, IHaveCreatedAt, IHaveUpdatedAt
{
    use THasIdString;
    use THasCreatedAt;
    use THasUpdatedAt;

    public const FIELD__ENTITY = '__entity__';
    public const FIELD__USER = '__user__';

    public function getEntity(): string
    {
        return $this->getAttributeString(static::FIELD__ENTITY, '');
    }

    public function getUser(): string
    {
        return $this->getAttributeString(static::FIELD__USER, '');
    }
}
