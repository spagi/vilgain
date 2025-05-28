<?php

declare(strict_types=1);
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;


final class UserPasswordHasherProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor, // Nebo specifický typ jako PersistProcessorInterface
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ?LoggerInterface $logger = null // Logger je volitelný
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if ($data instanceof User && $data->getPlainPassword()) {
            $this->logger?->info(sprintf('UserPasswordHasherProcessor: Hashing password for user "%s".', $data->getUserIdentifier()));

            $hashedPassword = $this->passwordHasher->hashPassword(
                $data,
                $data->getPlainPassword()
            );
            $data->setPassword($hashedPassword);

            $data->eraseCredentials();
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}