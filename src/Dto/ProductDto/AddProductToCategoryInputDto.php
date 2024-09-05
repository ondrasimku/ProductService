<?php

namespace App\Dto\ProductDto;

use App\Dto\InputInterface;

class AddProductToCategoryInputDto implements InputInterface
{
    public int $categoryId;
}
