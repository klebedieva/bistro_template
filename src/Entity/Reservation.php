<?php

namespace App\Entity;

use App\Enum\ReservationStatus;
use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Restaurant table reservation request.
 *
 * Represents a booking request from the website. Admins review and update its
 * status in the back office (confirmation, cancellation, no-show tracking).
 *
 * Invariants/notes:
 * - Status uses ReservationStatus enum for type safety (pending|confirmed|cancelled|completed|no_show)
 * - isConfirmed mirrors status=confirmed for UI convenience
 * - Date and time are stored separately (time as HH:MM string)
 */
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservations')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 30)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\\d{2}:\\d{2}$/', message: 'Invalid time format')]
    private ?string $time = null; // HH:MM

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $guests = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'string', enumType: ReservationStatus::class, options: ['default' => ReservationStatus::PENDING->value])]
    private ?ReservationStatus $status = ReservationStatus::PENDING;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isConfirmed = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $confirmationMessage = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = ReservationStatus::PENDING;
        $this->isConfirmed = false;
    }

    public function getId(): ?int { return $this->id; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(string $phone): self { $this->phone = $phone; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getTime(): ?string { return $this->time; }
    public function setTime(string $time): self { $this->time = $time; return $this; }

    public function getGuests(): ?int { return $this->guests; }
    public function setGuests(int $guests): self { $this->guests = $guests; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    /**
     * Get reservation status
     *
     * Returns the current status as ReservationStatus enum for type safety.
     *
     * @return ReservationStatus|null Current reservation status
     */
    public function getStatus(): ?ReservationStatus { return $this->status; }
    
    /**
     * Set reservation status
     *
     * Updates the reservation status and automatically synchronizes the isConfirmed flag.
     * Supports both enum and string for backward compatibility during migration.
     *
     * @param ReservationStatus|string $status New status (enum preferred, string supported for compatibility)
     * @return self
     */
    public function setStatus(ReservationStatus|string $status): self
    {
        // Support both enum and string for backward compatibility during migration
        // This allows existing code using strings to continue working
        $this->status = $status instanceof ReservationStatus ? $status : ReservationStatus::from($status);
        
        // Automatically update isConfirmed flag when status changes to confirmed
        // This ensures data consistency between status and isConfirmed fields
        $this->isConfirmed = ($this->status === ReservationStatus::CONFIRMED);
        
        return $this;
    }

    public function isConfirmed(): ?bool { return $this->isConfirmed; }
    public function setIsConfirmed(bool $isConfirmed): self { $this->isConfirmed = $isConfirmed; return $this; }

    public function getConfirmedAt(): ?\DateTimeImmutable { return $this->confirmedAt; }
    public function setConfirmedAt(?\DateTimeImmutable $confirmedAt): self { $this->confirmedAt = $confirmedAt; return $this; }

    public function getConfirmationMessage(): ?string { return $this->confirmationMessage; }
    public function setConfirmationMessage(?string $confirmationMessage): self { $this->confirmationMessage = $confirmationMessage; return $this; }
}


