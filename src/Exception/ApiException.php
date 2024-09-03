<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private array $errors;
    /**
     * @param array<string> $headers
     * @param array<string> $messages
     */
    public function __construct(
        array $messages,
        int $statusCode,
        Exception $previous = null,
        array $headers = array(),
        int $code = 0
    ) {
        $errorMessage = null;
        if (empty($messages)) {
            $errorMessage = "";
        }
        $this->errors = $messages;
        $errorMessage = implode(", ", $messages);
        parent::__construct($statusCode, $errorMessage, $previous, $headers, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
