<?php

namespace App\Controller\ProductController;

use App\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[Route("/api", name: "api_")]
readonly class ProductController
{
    public function __construct(
        private SerializerInterface $serializer
    ) {
    }

    #[Route("/products", name: "product_create", methods: ["POST"])]
    public function createProduct(): Response
    {
        $responseData = $this->serializer->serialize([], 'json');
        return new ApiResponse($responseData, Response::HTTP_CREATED);
    }
}
