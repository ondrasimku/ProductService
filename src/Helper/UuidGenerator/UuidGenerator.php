<?php

namespace App\Helper\UuidGenerator;

use Symfony\Component\Uid\Uuid;

class UuidGenerator
{
    public static function generateUuid(): string
    {
        return Uuid::v7()->toString();
    }
}