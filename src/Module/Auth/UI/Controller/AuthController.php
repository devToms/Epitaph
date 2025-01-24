<?php

declare(strict_types=1);

namespace App\Module\Auth\UI\Controller;

use App\Module\Auth\Application\Command\LoginUser\LoginUserCommand;
use App\Module\Auth\Application\Command\LogoutUser\LogoutUserCommand;
use App\Module\Auth\Application\DTO\LoginUserDTO;
use App\Module\Auth\Application\DTO\LogoutUserDTO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Cookie;


#[Route('/api/v1')]
class AuthController
{
    public function __construct(
        protected readonly CommandBusInterface $commandBus
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
            return new Response(
                json_encode(['success' => false, 'errors' => $dto->getErrors()]),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $result = $this->commandBus->handle(
                new LoginUserCommand($dto)
            );

            return new Response(json_encode(['success' => true, 'data' => $result]), Response::HTTP_OK);
        } catch (AuthenticationException $e) {
            return new Response(
                json_encode(['success' => false, 'message' => $e->getMessage()]),
                Response::HTTP_UNAUTHORIZED
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
            $result = $this->commandBus->handle(
                new LogoutUserCommand($dto)
            );

            return new Response(json_encode(['success' => true, 'data' => $result]), Response::HTTP_OK);
        } catch (AuthenticationException $e) {
            return new Response(
                json_encode(['success' => false, 'message' => $e->getMessage()]),
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
    
     
}
