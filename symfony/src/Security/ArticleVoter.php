<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const EDIT = 'EDIT_ARTICLE';
    public const DELETE = 'DELETE_ARTICLE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Article) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Article $article */
        $article = $subject;

        return match ($attribute) {
            self::DELETE, self::EDIT => $user === $article->getAuthor() && $this->security->isGranted('ROLE_AUTHOR'),
            default => false,
        };

    }
}