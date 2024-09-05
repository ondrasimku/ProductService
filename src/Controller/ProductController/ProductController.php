<?php

namespace App\Controller\ProductController;

use App\Dto\ProductDto\AddProductToCategoryInputDto;
use App\Dto\ProductDto\CreateProductDto;
use App\Response\ApiResponse;
use App\Service\ProductService\ProductService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[Route("/api", name: "api_")]
readonly class ProductController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ProductService $productService,
    ) {
    }

    #[Route("/products", name: "product_create", methods: ["POST"])]
    public function createProduct(CreateProductDto $createProductDto): Response
    {
        $product = $this->productService->createProduct($createProductDto);
        $responseData = $this->serializer->serialize($product, 'json', ["groups" => ["product"]]);
        return new ApiResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route("/products/{id}:addCategory", name: "product_add_category", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function addToCategory(AddProductToCategoryInputDto $addProductToCategoryInputDto, int $id): Response
    {
        $product = $this->productService->addToCategory($id, $addProductToCategoryInputDto);
        $responseData = $this->serializer->serialize($product, 'json', ["groups" => ["product"]]);
        return new ApiResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route("/products", name: "product_get_all", methods: ["GET"])]
    public function getAll(): Response
    {
        $products = $this->productService->getAll();
        $responseData = $this->serializer->serialize($products, 'json', ["groups" => ["product"]]);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/products/{id}", name: "product_get_by_id", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function getById(int $id): Response
    {
        $product = $this->productService->getById($id);
        $responseData = $this->serializer->serialize($product, 'json', ["groups" => ["product"]]);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/products/{id}", name: "product_delete_by_id", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function deleteById(int $id): Response
    {
        $this->productService->removeById($id);
        $responseData = $this->serializer->serialize([], 'json');
        return new ApiResponse($responseData, Response::HTTP_NO_CONTENT);
    }
}
