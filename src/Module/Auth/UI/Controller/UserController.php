<?php

declare(strict_types=1);

namespace App\Module\Auth\UI\Controller;

use App\Module\Auth\Application\Command\CreateUser\CreateUserCommand as CreateUserCommandEvent;
use App\Module\Auth\Application\DTO\CreateUserDTO;
use App\Module\Auth\Application\Query\FindUserByEmail\FindUserByEmailQuery;
use App\Module\Auth\Domain\Entity\User;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use App\Common\Application\Bus\QueryBus\QueryBusInterface;
use App\Common\Application\Response\ResponseBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/v1')]
class UserController extends AbstractController
{
    public function __construct(
        protected readonly CommandBusInterface $commandBus,
        protected readonly QueryBusInterface $queryBus,
        protected readonly JWTTokenManagerInterface $JWTTokenManager,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ResponseBuilderInterface $responseBuilder
    ) {
    }

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Register user',
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
    #[OA\RequestBody(content: new Model(type: CreateUserDTO::class))]
    #[Route('/register', methods: [Request::METHOD_POST])]
    public function register(#[ValueResolver('create_user_dto')] CreateUserDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->responseBuilder->buildResponse(
                new CommandResult(false, Response::HTTP_BAD_REQUEST),
                null,
                'Validation errors occurred.',
                ['errors' => $dto->getErrors()] 
            );
        }

        $commandResult = $this->commandBus->handle(new CreateUserCommandEvent($dto));
        $queryResult = $this->queryBus->handle(new FindUserByEmailQuery($dto->email));
        
        if ($queryResult->data !== null) {
            $user = $this->entityManager->getReference(User::class, $queryResult->data['id']);
        }

        return $this->responseBuilder->buildResponse(
            $commandResult,
            'Successfully registered.',
            'Something went wrong while registering.',
            $token !== null ? ['token' => $token] : null
        );
    }
}
