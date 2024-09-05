<?php

namespace App\Dto\CategoryDto;

use App\Dto\InputInterface;

class UpdateCategoryDto implements InputInterface
{
    public ?string $title = null;
    public ?int $parentId = null;
    public ?bool $isActive = null;
}
