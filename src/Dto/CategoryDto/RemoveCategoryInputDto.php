<?php

namespace App\Dto\CategoryDto;

use App\Dto\InputInterface;

class RemoveCategoryInputDto implements InputInterface
{
    public ?int $newParentId = null;
}
