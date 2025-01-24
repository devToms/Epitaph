<?php


declare(strict_types=1);

namespace App\Common\Application\Response;

use App\Common\Application\BusResult\BusResultInterface;
use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\BusResult\QueryResult;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseBuilder implements ResponseBuilderInterface
{
    public function buildResponse(
        BusResultInterface $result, 
        ?string $successMessage = null, 
        ?string $errorMessage = null,
        ?array $data = null 
    ): JsonResponse
    {
        $responseBody = match (true) {
            $result instanceof QueryResult && $result->data === null => [
                'success' => false,
                'message' => $errorMessage ?? 'No data found.',
            ],
            $result instanceof CommandResult && $result->success => [
                'success' => true,
                'message' => $successMessage ?? 'Operation completed successfully.',
            ],
            default => [
                'success' => false,
                'message' => $errorMessage ?? 'An error occurred.',
            ]
        };


        if (($responseBody['success'] ?? false) && $data !== null) {
            $responseBody['data'] = $data;
        }

        return new JsonResponse($responseBody, $result->statusCode);
    }
}



// declare(strict_types=1);

// namespace App\Common\Application\Response;

// use App\Common\Application\BusResult\BusResultInterface;
// use App\Common\Application\BusResult\CommandResult;
// use App\Common\Application\BusResult\QueryResult;
// use Symfony\Component\HttpFoundation\JsonResponse;

// class ResponseBuilder implements ResponseBuilderInterface
// {
//     public function buildResponse(BusResultInterface $result, ?string $successMessage = null, ?string $errorMessage = null): JsonResponse
//     {
//         $responseBody = match (true) {
//             $result instanceof QueryResult && $result->data === null => [
//                 'success' => false,
//                 'message' => $errorMessage ?? 'No data found.',
//             ],
//             $result instanceof CommandResult && $result->success => [
//                 'success' => true,
//                 'message' => $successMessage ?? 'Operation completed successfully.',
//             ],
//             default => [
//                 'success' => false,
//                 'message' => $errorMessage ?? 'An error occurred.',
//             ]
//         };

//         return new JsonResponse($responseBody, $result->statusCode);
//     }
// }
