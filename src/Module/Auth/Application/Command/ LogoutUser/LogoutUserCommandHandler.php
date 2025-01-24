<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\Command\LogoutUser;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use App\Common\Application\Command\CommandHandlerInterface;
use App\Common\Application\BusResult\CommandResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class LogoutUserCommandHandler implements CommandHandlerInterface
{
    public function __construct() {}

    public function __invoke(LogoutUserCommand $command): CommandResult
    {
        try {
          
            $response = new Response();
        
            $response->headers->setCookie(new Cookie('PHPSESSID', '', time() - 3600, $command->dto->path));
            
            $response->setContent(json_encode([
                'success' => true,
                'message' => 'User logged out.'
            ]));
    
            $response->setStatusCode(Response::HTTP_OK);

        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new CommandResult(success: true, statusCode: Response::HTTP_CREATED);
    }
}
