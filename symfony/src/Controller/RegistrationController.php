<?php

declare(strict_types=1);

namespace App\Controller;


use App\Dto\RegistrationRequestDto;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly UserFactory $userFactory
    ) {}

    #[Route('/auth/register', name: 'auth_register_dto', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegistrationRequestDto $registrationRequest
    ): JsonResponse
    {

        try {
            $user = $this->userFactory->createFromDto($registrationRequest);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid role provided during user creation: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'An account with this email already exists.'], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Could not save user due to a server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse(json_decode($jsonUser, true), Response::HTTP_CREATED);
    }
}
