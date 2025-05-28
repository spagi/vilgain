<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\RegistrationRequestDto;
use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function createFromDto(RegistrationRequestDto $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setName($dto->name);
        $user->setPlainPassword($dto->password);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
        try {
            $roleEnum = UserRole::from($dto->role);
            $user->setRole($roleEnum);
        } catch (\ValueError $e) {
            throw $e;
        }

        return $user;
    }
}