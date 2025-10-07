<?php
namespace jeyroik\interfaces\entities;

use jeyroik\interfaces\attributes\IHaveCreatedAt;
use jeyroik\interfaces\attributes\IHaveIdString;
use jeyroik\interfaces\attributes\IHaveUpdatedAt;
use jeyroik\interfaces\IHaveAttributes;

interface IApiEntity extends IHaveAttributes, IHaveIdString, IHaveCreatedAt, IHaveUpdatedAt
{
    public const FIELD__USER = '__user__';
    public const FIELD__SYSTEM_ID = '_id';

    public function getUser(): string;
}
