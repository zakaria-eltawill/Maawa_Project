<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ConflictException extends Exception
{
    /**
     * Create a new conflict exception instance.
     */
    public function __construct(string $message = 'Conflict', int $code = 409)
    {
        parent::__construct($message, $code);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'type' => 'about:blank',
            'title' => 'Conflict',
            'status' => 409,
            'detail' => $this->getMessage(),
        ], 409);
    }
}

