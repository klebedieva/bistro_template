<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * ValidReservationRequest
 *
 * Class-level constraint to validate reservation date/time rules on DTO level.
 * This centralizes validation and removes duplicated checks from controllers.
 *
 * Why class-level?
 * - We validate a combination of fields (date + time), so a property-level
 *   constraint is not sufficient.
 * - Keeping these rules next to the DTO ensures the same logic is applied
 *   across all entry points (forms, APIs) using that DTO.
 *
 * Messages are in French to match the rest of the application.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ValidReservationRequest extends Constraint
{
    public string $invalidDateMessage = 'La date de réservation est invalide ou déjà passée';
    public string $invalidTimeMessage = 'L\'heure doit être au format HH:MM entre 14:00 et 22:30 par pas de 30 minutes';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}


