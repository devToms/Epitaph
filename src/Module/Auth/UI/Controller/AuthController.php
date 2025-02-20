<?php

declare(strict_types=1);

namespace App\Module\Auth\UI\Controller;

use App\Module\Auth\Application\Command\LoginUser\LoginUserCommand;
use App\Module\Auth\Application\Command\LogoutUser\LogoutUserCommand;
use App\Module\Auth\Application\DTO\LoginUserDTO;
use App\Module\Auth\Application\DTO\LogoutUserDTO;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use App\Common\Application\Response\ResponseBuilderInterface;

#[Route('/api/v1')]
class AuthController
{
    public function __construct(
        protected readonly CommandBusInterface $commandBus,
        protected readonly ResponseBuilderInterface $responseBuilder // Dodajemy ResponseBuilder
    ) {}

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Authenticate user',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'data',
                    properties: [new OA\Property(property: 'token', type: 'string')],
                    type: 'object',
                ),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: LoginUserDTO::class))]
    #[Route('/login', methods: ['POST'])]
    public function login(#[ValueResolver('login_user_dto')] LoginUserDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->responseBuilder->buildResponse(
                new CommandResult(false, Response::HTTP_BAD_REQUEST),
                null,
                'Validation errors occurred.',
                ['errors' => $dto->getErrors()]
            );
        }

        try {
            $result = $this->commandBus->handle(
                new LoginUserCommand($dto)
            );

            return $this->responseBuilder->buildResponse(
                new CommandResult(true, Response::HTTP_OK),
                'User logged in successfully.',
                null,
                ['token' => $result] 
            );
        } catch (AuthenticationException $e) {
            return $this->responseBuilder->buildResponse(
                new CommandResult(false, Response::HTTP_UNAUTHORIZED),
                null,
                $e->getMessage()
            );
        }
    }

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Logout user',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: LogoutUserDTO::class))]
    #[Route('/logout', methods: ['POST'])]
    public function logout(#[ValueResolver('logout_user_dto')] LogoutUserDTO $dto): Response
    {
        try {
            $this->commandBus->handle(
                new LogoutUserCommand($dto)
            );

            return $this->responseBuilder->buildResponse(
                new CommandResult(true, Response::HTTP_OK),
                'User logged out successfully.'
            );
        } catch (AuthenticationException $e) {
            return $this->responseBuilder->buildResponse(
                new CommandResult(false, Response::HTTP_UNAUTHORIZED),
                null,
                $e->getMessage()
            );
        }
    }
}
