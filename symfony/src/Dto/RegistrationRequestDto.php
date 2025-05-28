<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\UserRole; // Pro validaci povolených hodnot role

class RegistrationRequestDto
{
    #[Assert\NotBlank(message: "Email should not be blank.")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Password should not be blank.")]
    #[Assert\Length(min: 8, minMessage: "Password should be at least {{ limit }} characters long.")]

    public ?string $password = null;

    #[Assert\NotBlank(message: "Name should not be blank.")]
    public ?string $name = null;

    #[Assert\NotBlank(message: "Role should not be blank.")]
    #[Assert\Choice(
        callback: [UserRole::class, 'getValues'],
        message: "Invalid role '{{ value }}'. Allowed roles are: admin, author, reader."
    )]
    public ?string $role = null;
}