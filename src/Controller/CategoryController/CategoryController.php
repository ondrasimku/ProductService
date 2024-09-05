<?php

namespace App\Controller\CategoryController;

use App\Dto\CategoryDto\CreateCategoryInputDto;
use App\Dto\CategoryDto\RemoveCategoryInputDto;
use App\Dto\CategoryDto\UpdateCategoryDto;
use App\Response\ApiResponse;
use App\Service\CategoryService\CategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[Route("/api", name: "api_")]
readonly class CategoryController
{
    public function __construct(
        private SerializerInterface $serializer,
        private CategoryService $categoryService,
    ) {
    }

    #[Route("/categories", name: "category_create", methods: ["POST"])]
    public function createCategory(CreateCategoryInputDto $categoryInputDto): Response
    {
        $category = $this->categoryService->createCategory($categoryInputDto);
        $responseData = $this->serialize($category);
        return new ApiResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route("/categories", name: "category_get_all", methods: ["GET"])]
    public function getAll(): Response
    {
        $categories = $this->categoryService->getAll();
        $responseData = $this->serialize($categories);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/categories:render", name: "category_render", methods: ["GET"])]
    public function getRender(): Response
    {
        $categories = $this->categoryService->getRootActiveCategories();
        // We use normalize here so that we can filter out inActive categories afterward
        // BEGIN - THIS SHOULD BE DONE BY NORMALIZER, BUT CUSTOM NORMALIZER REFUSES TO WORK
        $responseData = $this->serializer->normalize(
            $categories,
            'json',
            [
                "groups" => "category",
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER =>
                    function (object $object, ?string $format, array $context): int {
                        return $object->getId();
                    },
            ]
        );

        foreach ($responseData as &$data) {
            $this->filterInactiveChildren($data);
        }
        // END - THIS SHOULD BE DONE BY NORMALIZER, BUT CUSTOM NORMALIZER REFUSES TO WORK
        $responseData = $this->serializer->encode($responseData, 'json');
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/categories:rootActive", name: "category_get_root_active", methods: ["GET"])]
    public function getAllRootActive(): Response
    {
        $categories = $this->categoryService->getRootActiveCategories();
        $responseData = $this->serialize($categories);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/categories:rootInActive", name: "category_get_root_inactive", methods: ["GET"])]
    public function getAllRootInActive(): Response
    {
        $categories = $this->categoryService->getRootInActiveCategories();
        $responseData = $this->serialize($categories);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/categories/{id}", name: "category_get_by_id", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function getById(int $id): Response
    {
        $category = $this->categoryService->getById($id);
        $responseData = $this->serialize($category);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    #[Route("/categories/{id}:delete", name: "category_remove_by_id", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function removeById(int $id, RemoveCategoryInputDto $removeCategoryInputDto): Response
    {
        $this->categoryService->removeById($id, $removeCategoryInputDto);
        $responseData = $this->serialize([]);
        return new ApiResponse($responseData, Response::HTTP_NO_CONTENT);
    }
    #[Route("/categories/{id}", name: "category_patch", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function patchById(int $id, UpdateCategoryDto $updateCategoryInputDto): Response
    {
        $category = $this->categoryService->patchById($id, $updateCategoryInputDto);
        $responseData = $this->serialize($category);
        return new ApiResponse($responseData, Response::HTTP_OK);
    }

    private function serialize(mixed $data): string
    {
        return $this->serializer->serialize(
            $data,
            'json',
            [
                "groups" => "category",
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER =>
                    function (object $object, ?string $format, array $context): int {
                        return $object->getId();
                    },
            ]
        );
    }

    private function filterInactiveChildren(array &$data): void
    {
        if (isset($data['children']) && is_array($data['children'])) {
            $activeChildren = [];
            foreach ($data['children'] as $child) {
                $this->filterInactiveChildren($child);
                if (isset($child['isActive']) && $child['isActive'] === true) {
                    $activeChildren[] = $child;
                }
            }
            $data['children'] = $activeChildren;
        }
    }
}
