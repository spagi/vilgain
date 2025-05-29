<?php

declare(strict_types=1);

namespace App\Tests\Functional;


use App\Entity\Article;
use App\Entity\UserRole;
use App\Tests\ApiTestCaseBase;

final class ArticleResourceTest extends ApiTestCaseBase
{
    public function testGetArticlesCollectionIsPublic(): void
    {

        $this->client->request('GET', '/articles');

        $this->assertResponseIsSuccessful();

        $this->assertJsonContains(['totalItems' => 2]);

    }

    public function testReaderCannotCreateArticle(): void
    {

        $this->createUser('reader_creator@example.com', 'password', UserRole::READER, 'Reader Creator');
        $token = $this->login( 'reader_creator@example.com', 'password');

        $this->client->request('POST', '/articles', [
            'auth_bearer' => $token,
            'json' => ['title' => 'Reader Article', 'content' => 'Content by reader'],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']
        ]);

        $this->assertResponseStatusCodeSame(403, "Reader should not be able to create articles.");
    }

    public function testAuthorCanCreateArticle(): void
    {
        $this->createUser('author_creator@example.com', 'password', UserRole::AUTHOR, 'Author Creator');
        $token = $this->login('author_creator@example.com', 'password');

        $this->client->request('POST', '/articles', [
            'auth_bearer' => $token,
            'json' => ['title' => 'Author Article', 'content' => 'Content by author'],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(201); // HTTP 201 Created
        $this->assertJsonContains(['title' => 'Author Article']);
    }

    public function testAdminCanCreateArticle(): void
    {
        $this->createUser('admin_creator@example.com', 'password', UserRole::ADMIN, 'Admin Creator');
        $token = $this->login('admin_creator@example.com', 'password');

        $this->client->request('POST', '/articles', [
            'auth_bearer' => $token,
            'json' => ['title' => 'Admin Article', 'content' => 'Content by admin'],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['title' => 'Admin Article']);
    }


    // Testy pro úpravu (PUT)
    public function testAuthorCanEditOwnArticle(): void
    {
        $author = $this->createUser('author_editor@example.com', 'password', UserRole::AUTHOR, 'Author Editor');
        $token = $this->login( 'author_editor@example.com', 'password');

        $article = new Article();
        $article->setTitle('Original Title');
        $article->setContent('Original content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $this->client->request('PUT', '/articles/' . $article->getId(), [
            'auth_bearer' => $token,
            'json' => ['title' => 'Updated Title by Author', 'content' => 'Updated content'],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/ld+json']
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['title' => 'Updated Title by Author']);
    }

    public function testAuthorCannotEditOthersArticle(): void
    {
        $author1 = $this->createUser('author_one@example.com', 'password', UserRole::AUTHOR, 'Author One');
        $token1 = $this->login( 'author_one@example.com', 'password');

        $author2 = $this->createUser('author_two@example.com', 'password', UserRole::AUTHOR, 'Author Two');

        $articleByAuthor2 = new Article();
        $articleByAuthor2->setTitle('Article by Author Two');
        $articleByAuthor2->setContent('Content');
        $articleByAuthor2->setAuthor($author2);
        $this->entityManager->persist($articleByAuthor2);
        $this->entityManager->flush();


        $this->client->request('PUT', '/articles/' . $articleByAuthor2->getId(), [
            'auth_bearer' => $token1,
            'json' => ['title' => 'Attempted Edit by Author One'],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/ld+json']
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanEditAnyArticle(): void
    {
        $this->createUser('admin_editor@example.com', 'password', UserRole::ADMIN, 'Admin Editor');
        $tokenAdmin = $this->login( 'admin_editor@example.com', 'password');

        $author = $this->createUser('another_author@example.com', 'password', UserRole::AUTHOR, 'Another Author');
        $articleByAuthor = new Article();
        $articleByAuthor->setTitle('Article by Another Author');
        $articleByAuthor->setContent('Content');
        $articleByAuthor->setAuthor($author);
        $this->entityManager->persist($articleByAuthor);
        $this->entityManager->flush();

        $this->client->request('PUT', '/articles/' . $articleByAuthor->getId(), [
            'auth_bearer' => $tokenAdmin,
            'json' => ['title' => 'Updated by Admin'],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/ld+json']
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['title' => 'Updated by Admin']);
    }

    public function testReaderCannotDeleteArticle(): void
    {
        $admin = $this->createUser('del_admin@example.com', 'password', UserRole::ADMIN);
        $article = new Article();
        $article->setTitle('To Be Deleted?');
        $article->setContent('Content');
        $article->setAuthor($admin);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $this->createUser('deletereader@example.com', 'password', UserRole::READER);

        $tokenReader = $this->login('deletereader@example.com', 'password');
        $this->client->request('DELETE', '/articles/' . $article->getId(), [
            'auth_bearer' => $tokenReader,
        ]);
        $this->assertResponseStatusCodeSame(403);
    }


    public function testAuthorCanDeleteOwnArticle(): void
    {
        $author = $this->createUser('del_author_owner@example.com', 'password', UserRole::AUTHOR);
        $token = $this->login( 'del_author_owner@example.com', 'password');

        $article = new Article();
        $article->setTitle('Author Deletable Article');
        $article->setContent('Content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        $articleId = $article->getId();

        $this->client->request('DELETE', '/articles/' . $articleId, [
            'auth_bearer' => $token,
        ]);
        $this->assertResponseStatusCodeSame(204);

    }


    public function testAuthorCannotDeleteOthersArticle(): void
    {
        $this->createUser('del_author_one@example.com', 'password', UserRole::AUTHOR);
        $token1 = $this->login( 'del_author_one@example.com', 'password');

        $author2 = $this->createUser('del_author_two@example.com', 'password', UserRole::AUTHOR);
        $articleByAuthor2 = new Article();
        $articleByAuthor2->setTitle('Article by Del Author Two');
        $articleByAuthor2->setContent('Content');
        $articleByAuthor2->setAuthor($author2);
        $this->entityManager->persist($articleByAuthor2);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/articles/' . $articleByAuthor2->getId(), [
            'auth_bearer' => $token1,
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanDeleteAnyArticle(): void
    {

        $this->createUser('del_admin_any@example.com', 'password', UserRole::ADMIN);
        $tokenAdmin = $this->login( 'del_admin_any@example.com', 'password');

        $author = $this->createUser('del_another_author@example.com', 'password', UserRole::AUTHOR);
        $articleByAuthor = new Article();
        $articleByAuthor->setTitle('Article to be Deleted by Admin');
        $articleByAuthor->setContent('Content');
        $articleByAuthor->setAuthor($author);
        $this->entityManager->persist($articleByAuthor);
        $this->entityManager->flush();
        $articleId = $articleByAuthor->getId();

        $this->client->request('DELETE', '/articles/' . $articleId, [
            'auth_bearer' => $tokenAdmin,
        ]);
        $this->assertResponseStatusCodeSame(204);

    }
}