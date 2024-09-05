<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class CategoryIdNotFoundException extends ApiException
{
    /**
     * @param array<string> $headers
     * @param array<string> $messages
     */
    public function __construct(
        array $messages = [],
        int $statusCode = Response::HTTP_NOT_FOUND,
        Exception $previous = null,
        array $headers = array(),
        int $code = 0
    ) {
        parent::__construct($messages, $statusCode, $previous, $headers, $code);
    }
}