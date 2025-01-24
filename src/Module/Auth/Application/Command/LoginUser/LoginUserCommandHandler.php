<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\Command\LoginUser;

use App\Module\Auth\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Common\Application\Command\CommandHandlerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Common\Application\BusResult\CommandResult;

#[AsMessageHandler]
class LoginUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        protected UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function __invoke(LoginUserCommand $command): array
    {
        $user = $this->userRepository->findByEmail($command->dto->email);

        if (!$user || !$this->passwordHasher->verify($user->getPassword(), $command->dto->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return [
            'token' => $this->jwtManager->create($user),
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ],
        ];
    }
}
