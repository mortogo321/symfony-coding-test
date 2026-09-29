<?php

namespace App\Controller\Api;

use App\DTO\RegisterUserRequest;
use App\Entity\User;
use App\Message\UserRegisteredMessage;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus
    ) {}

    #[Route('/api/register-user', name: 'app_api_register_user', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterUserRequest $request
    ): JsonResponse {
        // Create User entity
        $user = new User();
        $user->setFullName($request->fullName);
        $user->setEmail($request->email);
        $user->setPhone($request->phone);

        // Save to database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Email is already registered',
                'errors' => null,
            ], JsonResponse::HTTP_CONFLICT);
        }

        // Dispatch to RabbitMQ
        $this->messageBus->dispatch(new UserRegisteredMessage(
            $user->getId(),
            $user->getEmail(),
            $user->getFullName()
        ));

        return $this->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
            ],
        ], JsonResponse::HTTP_CREATED);
    }
}
