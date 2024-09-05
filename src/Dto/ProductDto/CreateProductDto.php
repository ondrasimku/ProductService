<?php

namespace App\Dto\ProductDto;

use App\Dto\InputInterface;

class CreateProductDto implements InputInterface
{
    public ?string $productName = null;
    public ?string $productDescription = null;
    public ?array $categoryIds = [];
}
