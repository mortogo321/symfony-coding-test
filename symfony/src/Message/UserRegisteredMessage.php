<?php

namespace App\Message;

final class UserRegisteredMessage
{
    public function __construct(
        private int $userId,
        private string $email,
        private string $fullName
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }
}
