<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reservation Creation Request Data Transfer Object
 *
 * DTO for validating reservation submissions from API clients.
 * Used with Symfony Validator to ensure data integrity before creating reservations.
 *
 * All validation messages are in French to match the application's language.
 */
class ReservationCreateRequest
{
    #[Assert\NotBlank(message: 'Le prénom est requis')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit contenir au moins 2 caractères', maxMessage: 'Le prénom ne peut pas dépasser 100 caractères')]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: 'Le nom est requis')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins 2 caractères', maxMessage: 'Le nom ne peut pas dépasser 100 caractères')]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'L\'email est requis')]
    #[Assert\Email(message: 'L\'email doit être valide')]
    #[Assert\Length(max: 255, maxMessage: 'L\'email ne peut pas dépasser 255 caractères')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Le numéro de téléphone est requis')]
    #[Assert\Length(min: 10, max: 20, minMessage: 'Le numéro de téléphone doit contenir au moins 10 caractères', maxMessage: 'Le numéro de téléphone ne peut pas dépasser 20 caractères')]
    public ?string $phone = null;

    #[Assert\NotBlank(message: 'La date est requise')]
    public ?string $date = null;

    #[Assert\NotBlank(message: 'L\'heure est requise')]
    public ?string $time = null;

    #[Assert\NotBlank(message: 'Le nombre de personnes est requis')]
    #[Assert\Type(type: 'integer', message: 'Le nombre de personnes doit être un entier')]
    #[Assert\Positive(message: 'Le nombre de personnes doit être positif')]
    #[Assert\LessThanOrEqual(value: 20, message: 'Le nombre de personnes ne peut pas dépasser 20')]
    public ?int $guests = null;

    #[Assert\Length(max: 500, maxMessage: 'Le message ne peut pas dépasser 500 caractères')]
    public ?string $message = null;
}

