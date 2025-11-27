<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserRequest
{
    #[Assert\NotBlank(message: 'Full name is required')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Full name must be at least {{ limit }} characters',
        maxMessage: 'Full name cannot exceed {{ limit }} characters'
    )]
    public ?string $fullName = null;

    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'The email "{{ value }}" is not valid')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Phone number is required')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9\s\-\(\)]{7,20}$/',
        message: 'Please enter a valid phone number'
    )]
    public ?string $phone = null;
}
