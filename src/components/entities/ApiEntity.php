<?php
namespace jeyroik\components;

use jeyroik\components\attributes\THasCreatedAt;
use jeyroik\components\attributes\THasIdString;
use jeyroik\components\attributes\THasUpdatedAt;
use jeyroik\interfaces\entities\IApiEntity;

class ApiEntity implements IApiEntity
{
    use THasIdString;
    use THasCreatedAt;
    use THasUpdatedAt;

    public function getUser(): string
    {
        return $this->getAttributeString(static::FIELD__USER, '');
    }
}
