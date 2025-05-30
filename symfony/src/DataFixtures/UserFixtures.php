<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const AUTHOR_USER_REFERENCE = 'author-user';
    public const READER_USER_REFERENCE = 'reader-user';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(\Doctrine\Persistence\ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setName('Admin User');
        $admin->setRole(UserRole::ADMIN);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $manager->persist($admin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        $author = new User();
        $author->setEmail('author@example.com');
        $author->setName('Author User');
        $author->setRole(UserRole::AUTHOR);
        $author->setPassword($this->passwordHasher->hashPassword($author, 'password123'));
        $manager->persist($author);
        $this->addReference(self::AUTHOR_USER_REFERENCE, $author);

        $reader = new User();
        $reader->setEmail('reader@example.com');
        $reader->setName('Reader User');
        $reader->setRole(UserRole::READER);
        $reader->setPassword($this->passwordHasher->hashPassword($reader, 'password123'));
        $manager->persist($reader);
        $this->addReference(self::READER_USER_REFERENCE, $reader);

        $manager->flush();
    }
}