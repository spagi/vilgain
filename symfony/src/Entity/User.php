<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use App\State\UserPasswordHasherProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email',entityClass: self::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/users',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can access the list of users."
        ),
        new Post(
            uriTemplate: '/users',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can create users.",
            validationContext: ['groups' => ['Default', 'user:create']]
        ),
        new Get(
            uriTemplate: '/users/{id}',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can access user details."
        ),
        new Put(
            uriTemplate: '/users/{id}',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can edit users.",
            validationContext: ['groups' => ['Default', 'user:update']],
            extraProperties: ['standard_put' => false]

        ),
        new Delete(
            uriTemplate: '/users/{id}',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can delete users."
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:read_simple'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(groups: ['user:create'])]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write','user:update'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password_hash = null;

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Groups(['user:write'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['user:read', 'user:write', 'user:read_simple'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', enumType: UserRole::class)]
    #[Assert\NotNull]
    #[Groups(['user:read', 'user:write'])]
    private UserRole $role = UserRole::READER; // Default role

    #[ORM\OneToMany(mappedBy: 'author_id', targetEntity: Article::class, orphanRemoval: true)]
    private Collection $articles;

    private ?LoggerInterface $logger = null; // Přidej jako property

    public function __construct(?LoggerInterface $logger = null) // Injectni přes konstruktor (DI)
    {
        $this->articles = new ArrayCollection();
        $this->logger = $logger; // Budeme potřebovat úpravu services.yaml nebo autowiring
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [$this->role->value];
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->password_hash;
    }

    public function setPassword(string $password_hash): static
    {
        $this->password_hash = $password_hash;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }
        return $this;
    }

}
