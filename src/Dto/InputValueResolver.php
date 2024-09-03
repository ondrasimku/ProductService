<?php

namespace App\Dto;

use App\Exception\ApiException;
use App\Validator\DtoValidator;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class InputValueResolver implements ValueResolverInterface
{
    private SerializerInterface $serializer;
    private DtoValidator $validator;

    public function __construct(SerializerInterface $serializer, DtoValidator $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }


    /**
     * @throws ReflectionException
     * @return array<mixed>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_subclass_of($argumentType, InputInterface::class)) {
            return [];
        }

        try {
            $dto = $this->serializer->deserialize($request->getContent(), $argument->getType(), 'json');
        } catch (\Exception $exception) {
            throw new ApiException(
                ["Cannot serialize, check request body for any errors\n" . $exception->getMessage()],
                400
            );
        }

        $errors = $this->validator->validate($dto);
        if (count($errors)) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = $error->getMessage();
            }
            throw new ApiException($errorsArray, 400);
        }

        return [
            $dto
        ];
    }
}
