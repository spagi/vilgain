<?php

declare(strict_types=1);

namespace App\EventListener;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
#[AsEntityListener(event: Events::prePersist, method: 'hashPasswordOnPrePersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'hashPasswordOnPreUpdate', entity: User::class)]
readonly class UserPasswordListener
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function hashPasswordOnPrePersist(User $user, LifecycleEventArgs $event): void
    {
        $this->hashPassword($user);
    }

    public function hashPasswordOnPreUpdate(User $user, LifecycleEventArgs $event): void
    {

        $this->hashPassword($user);
    }

    private function hashPassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setPassword($hashedPassword);
        }
    }
}