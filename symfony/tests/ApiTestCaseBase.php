<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\ArticleFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCaseBase extends ApiTestCase
{

    protected ?EntityManagerInterface $entityManager;
    protected ?UserPasswordHasherInterface $passwordHasher;
    private ?ORMExecutor $fixtureExecutor = null;
    private array $fixtures = [];

    protected ?Client $client;

    protected static EntityManagerInterface $staticEntityManager;


    protected function setUp(): void
    {

        self::bootKernel(['environment' => 'test']);
        $container = self::getContainer();
        $userFixtures = $container->get(UserFixtures::class);
        $articleFixtures = $container->get(ArticleFixtures::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->client =self::createClient([], [
            'base_uri' => 'http://127.0.0.1:8088',
        ]);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadatas); // Může vyhodit výjimku, pokud schéma neexistuje, obal do try-catch
        try {
            $schemaTool->dropSchema($metadatas);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        $schemaTool->createSchema($metadatas);

        if ($this->fixtureExecutor === null) {

            $this->fixtures = [
                $userFixtures,
                $articleFixtures,
            ];

            $purger = new ORMPurger($this->entityManager);

            $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
            $this->fixtureExecutor = new ORMExecutor($this->entityManager, $purger);
        }

        $this->fixtureExecutor->execute($this->fixtures, false);
    }

    protected function createUser(string $email, string $plainPassword, UserRole $role, string $name = 'Test User'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRole($role);

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function login( string $email, string $password): string
    {
        $response = $this->client->request('POST', '/auth/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        $this->assertResponseIsSuccessful('Login failed for ' . $email);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data, 'Token not found in login response for ' . $email);

        return $data['token'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager && $this->entityManager->isOpen()) {
            $this->entityManager->close();
        }
        $this->entityManager = null;
    }
}