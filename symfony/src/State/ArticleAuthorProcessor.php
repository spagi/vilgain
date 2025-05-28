<?php

declare(strict_types=1);

namespace App\State;
use ApiPlatform\Metadata\Operation;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ArticleAuthorProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly SymfonySecurity $security
    ) {}

    /**
     * @param Article $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Article && $operation->getMethod() === 'POST' && $data->getAuthor() === null) {
            $currentUser = $this->security->getUser();
            if (!$currentUser instanceof User) {
                throw new AccessDeniedException('User must be logged in and be an instance of App\Entity\User to create an article.');
            }
            $data->setAuthor($currentUser);
        }

        // Vždy předej zpracování dalšímu processoru
        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}