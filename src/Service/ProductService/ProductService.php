<?php

namespace App\Service\ProductService;

use App\Dto\ProductDto\AddProductToCategoryInputDto;
use App\Dto\ProductDto\CreateProductDto;
use App\Dto\ProductDto\RemoveProductFromCategoryInputDto;
use App\Entity\Product;
use App\Exception\ApiException;
use App\Repository\ProductRepository;
use App\Service\CategoryService\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class ProductService
{
    public function __construct(
        private CategoryService $categoryService,
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository
    ) {
    }
    public function createProduct(CreateProductDto $createProductDto): Product
    {
        // This throws exception automatically if some of the ids don't exist
        $categories = $this->categoryService->getCategoriesById($createProductDto->categoryIds);
        $product = new Product();
        $product->setProductDescription($createProductDto->productDescription)
            ->setProductName($createProductDto->productName);
        foreach ($categories as $category) {
            $category->addProduct($product);
            $this->entityManager->persist($category);
        }
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return $product;
    }

    public function getAll(): array
    {
        return $this->productRepository->findAll();
    }

    public function getById(int $id): Product
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new ApiException(["Product with ID $id was not found"], Response::HTTP_NOT_FOUND);
        }
        return $product;
    }

    public function removeById(int $id): void
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new ApiException(["Product with ID $id was not found"], Response::HTTP_NOT_FOUND);
        }
        $categories = $product->getCategories();
        foreach ($categories as $category) {
            $category->removeProduct($product);
            $this->entityManager->persist($category);
        }
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function addToCategory(int $productId, AddProductToCategoryInputDto $addProductToCategoryInputDto): Product
    {
        $product = $this->getById($productId);
        $newCategory = $this->categoryService->getById($addProductToCategoryInputDto->categoryId);
        $currentCategories = $product->getCategories();
        $currentCategoryIds = [];
        foreach ($currentCategories as $currentCategory) {
            $currentCategoryId = $currentCategory->getId();
            if ($currentCategoryId === $newCategory->getId()) {
                throw new ApiException(["Product is already part of this category"], Response::HTTP_CONFLICT);
            }
            $currentCategoryIds["$currentCategoryId"] = $currentCategory;
        }

        $newCategory->addProduct($product);
        $this->entityManager->persist($newCategory);
        $current = $newCategory->getParent();
        while ($current !== null) {
            $currentId = $current->getId();
            if (array_key_exists("$currentId", $currentCategoryIds)) {
                break;
            }
            $current->addProduct($product);
            $this->entityManager->persist($current);
            $current = $current->getParent();
        }
        $this->entityManager->flush();
        return $product;
    }

    public function removeFromCategory(int $id, RemoveProductFromCategoryInputDto $removeProductFromCategoryInputDto): Product
    {

    }
}
