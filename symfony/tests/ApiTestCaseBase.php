<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCaseBase extends ApiTestCase
{
    use ReloadDatabaseTrait;

    protected ?EntityManagerInterface $entityManager;
    protected ?UserPasswordHasherInterface $passwordHasher;
    protected ?Client $client;

    protected static EntityManagerInterface $staticEntityManager;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();

        self::$staticEntityManager = self::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool(self::$staticEntityManager);
        $metadatas = self::$staticEntityManager->getMetadataFactory()->getAllMetadata();

        try {
            error_log("Attempting to drop and create schema for tests...");
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadatas);
            error_log("Schema created successfully for tests.");
        } catch (\Exception $e) {
            error_log("Error creating schema for tests: " . $e->getMessage());
            throw $e;
        }
    }

    protected function setUp(): void
    {
       parent::setUp();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->client =self::createClient([], [
            'base_uri' => 'http://127.0.0.1:8088',
        ]);
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