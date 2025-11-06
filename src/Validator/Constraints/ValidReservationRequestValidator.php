<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for ValidReservationRequest constraint.
 *
 * Validates two things on the DTO:
 * - date: must be parseable and not in the past (today allowed)
 * - time: must match HH:MM, within 14:00..22:30 inclusive, in 30-minute steps
 *
 * Notes/Assumptions:
 * - We only check the date part for "not in the past"; same-day reservations
 *   are allowed (the service/backoffice can apply stricter business rules).
 * - Time window (14:00–22:30) and 30-minute increments match the UI slots.
 * - This validator is intentionally conservative; domain services can add
 *   additional checks (capacity/availability) without duplicating controller code.
 */
class ValidReservationRequestValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidReservationRequest) {
            return;
        }

        if (!is_object($value)) {
            return;
        }

        // Expecting properties on the DTO:
        // - date: string or \DateTimeInterface
        // - time: string in HH:MM
        $dateProp = $value->date ?? null;
        $timeProp = $value->time ?? null;

        // Validate date
        $dateOk = false;
        if ($dateProp instanceof \DateTimeInterface) {
            $date = \DateTimeImmutable::createFromInterface($dateProp);
            $dateOk = true;
        } else {
            try {
                $date = new \DateTimeImmutable((string) $dateProp);
                $dateOk = true;
            } catch (\Exception) {
                $dateOk = false;
            }
        }

        if (!$dateOk) {
            $this->context->buildViolation($constraint->invalidDateMessage)
                ->atPath('date')
                ->addViolation();
        } else {
            // Compare date part with today (allow today)
            $today = (new \DateTimeImmutable('today'));
            if ($date < $today) {
                $this->context->buildViolation($constraint->invalidDateMessage)
                    ->atPath('date')
                    ->addViolation();
            }
        }

        // Validate time format HH:MM with 30-min steps and business hours 14:00..22:30
        $time = (string) $timeProp;
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            $this->context->buildViolation($constraint->invalidTimeMessage)
                ->atPath('time')
                ->addViolation();
            return;
        }

        [$hh, $mm] = array_map('intval', explode(':', $time));
        $minutesOk = in_array($mm, [0, 30], true);
        // 14:00 (inclusive) .. 22:30 (inclusive)
        $withinHours = ($hh > 14 || ($hh === 14 && $mm >= 0)) && ($hh < 22 || ($hh === 22 && $mm <= 30));
        if (!$minutesOk || !$withinHours) {
            $this->context->buildViolation($constraint->invalidTimeMessage)
                ->atPath('time')
                ->addViolation();
        }
    }
}


