<?php

namespace App\Response;

use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends JsonResponse
{
    /**
     * @param array<string> $headers
     * @param array<mixed> $metadata
     */
    public function __construct(string $content = '', int $status = 200, array $headers = [], array $metadata = [])
    {
        // Ensure content is a valid JSON string
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Handle JSON decode error if needed
            $data = null;
            throw new ApiException(["Failed to decode JSON response"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        parent::__construct(["data" => $data, "metadata" => $metadata], $status, $headers);
    }
}
