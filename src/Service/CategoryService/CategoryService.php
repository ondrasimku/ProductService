<?php

namespace App\Service\CategoryService;

use App\Dto\CategoryDto\CreateCategoryInputDto;
use App\Dto\CategoryDto\RemoveCategoryInputDto;
use App\Dto\CategoryDto\UpdateCategoryDto;
use App\Entity\Category;
use App\Exception\ApiException;
use App\Exception\CategoryIdNotFoundException;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class CategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    ) {
    }
    public function createCategory(CreateCategoryInputDto $categoryInputDto): Category
    {
        $category = new Category();
        $category->setTitle($categoryInputDto->title)
            ->setActive(true);
        if ($categoryInputDto->parentId !== null) {
            $parent = $this->categoryRepository->find($categoryInputDto->parentId);
            if (!$parent) {
                throw new ApiException(["Parent category ID doesn't exist"], Response::HTTP_BAD_REQUEST);
            }
            $category->setParent($parent);
        }
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        return $category;
    }

    public function getAll(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getById(int $id): Category
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            throw new CategoryIdNotFoundException(["Category with ID $id was not found"], Response::HTTP_NOT_FOUND);
        }
        return $category;
    }

    public function getRootCategories(): array
    {
        return $this->categoryRepository->findRoot();
    }

    public function removeById(int $id, RemoveCategoryInputDto $removeCategoryInputDto): void
    {
        $category = $this->findOrThrowNotFound($id);
        if ($removeCategoryInputDto->newParentId == null) {
            $children = $category->getChildren();
            /** @var Category $child */
            foreach ($children as $child) {
                $child->setParent(null)
                    ->setActive(false);
                $this->entityManager->persist($child);
            }
        } else {
            // Handle case where a new parent is provided
            $newParent = $this->categoryRepository->find($removeCategoryInputDto->newParentId);
            if (!$newParent) {
                throw new CategoryIdNotFoundException(
                    ["New parent category with ID {$removeCategoryInputDto->newParentId} was not found"],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $children = $category->getChildren();
            foreach ($children as $child) {
                $child->setParent($newParent);
                $this->entityManager->persist($child);
            }
        }
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function getRootActiveCategories(): array
    {
        return $this->categoryRepository->findRootActive();
    }

    public function getRootInActiveCategories(): array
    {
        return $this->categoryRepository->findRootInActive();
    }

    public function patchById(int $id, UpdateCategoryDto $updateCategoryInputDto): Category
    {
        $category = $this->findOrThrowNotFound($id);
        if ($updateCategoryInputDto->parentId) {
            $category->setParent($updateCategoryInputDto->parentId);
        }
        if ($updateCategoryInputDto->title) {
            $category->setTitle($updateCategoryInputDto->title);
        }
        if ($updateCategoryInputDto->isActive) {
            $category->setActive($updateCategoryInputDto->isActive);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();
        return $category;
    }

    private function findOrThrowNotFound(int $id): Category
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            throw new CategoryIdNotFoundException(["Category with ID $id was not found"], Response::HTTP_NOT_FOUND);
        }
        return $category;
    }

    /**
     * @return array<Category>
     */
    public function getCategoriesById(array $ids): array
    {
        $notFoundIds = [];
        $categories = [];
        foreach ($ids as $key => $categoryId) {
            try {
                $categories[] = $this->getById($categoryId);
            } catch (CategoryIdNotFoundException $exception) {
                $notFoundIds[] = $categoryId;
            }
        }
        if (!empty($notFoundIds)) {
            $message = "Category with ID [";
            for ($i = 0; $i < count($notFoundIds); ++$i) {
                if ($i === 0) {
                    $message .= $notFoundIds[$i];
                } else {
                    $message .= "," . $notFoundIds[$i];
                }
            }
            $message .= "] was not found.";
            throw new ApiException([$message], Response::HTTP_BAD_REQUEST);
        }
        return $categories;
    }
}
