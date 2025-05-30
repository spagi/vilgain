<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(\Doctrine\Persistence\ObjectManager $manager): void
    {
        /** @var User $adminUser */
        $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class);
        /** @var User $authorUser */
        $authorUser = $this->getReference(UserFixtures::AUTHOR_USER_REFERENCE, User::class);

        $article1 = new Article();
        $article1->setTitle('Admin Article Fixture');
        $article1->setContent('Content by admin from fixture.');
        $article1->setAuthor($adminUser);
        $manager->persist($article1);

        $article2 = new Article();
        $article2->setTitle('Author Article Fixture');
        $article2->setContent('Content by author from fixture.');
        $article2->setAuthor($authorUser);
        $manager->persist($article2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}