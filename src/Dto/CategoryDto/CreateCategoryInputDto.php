<?php

namespace App\Dto\CategoryDto;

use App\Dto\InputInterface;

class CreateCategoryInputDto implements InputInterface
{
    public string $title;
    public ?int $parentId = null;
}
