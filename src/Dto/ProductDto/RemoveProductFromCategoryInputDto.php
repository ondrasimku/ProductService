<?php

namespace App\Dto\ProductDto;

use App\Dto\InputInterface;

class RemoveProductFromCategoryInputDto implements InputInterface
{
    public int $categoryId;
}
