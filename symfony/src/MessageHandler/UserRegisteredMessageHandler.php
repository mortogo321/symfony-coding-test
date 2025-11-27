<?php

namespace App\MessageHandler;

use App\Message\UserRegisteredMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserRegisteredMessageHandler
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {}

    public function __invoke(UserRegisteredMessage $message): void
    {
        // Handle the message (e.g., send welcome email, notify external services, etc.)
        $this->logger?->info('User registered', [
            'userId' => $message->getUserId(),
            'email' => $message->getEmail(),
            'fullName' => $message->getFullName(),
        ]);
    }
}
