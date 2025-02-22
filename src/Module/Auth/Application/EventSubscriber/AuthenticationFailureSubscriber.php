<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;


class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $response = $event->getResponse();

        $content = json_decode($response->getContent(), true);
        $content['success'] = false;

        $response->setContent(json_encode($content));
        $event->setResponse($response);

        $this->logger->error('Authentication failed', ['response' => $content]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
        ];
    }
}
