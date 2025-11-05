<?php

namespace App\Enum;

/**
 * Reservation Status Enum
 *
 * Represents all possible states of a restaurant reservation.
 * Used for type safety and to prevent magic string usage throughout the codebase.
 *
 * Status workflow:
 * - PENDING: Initial state when reservation is created (default)
 * - CONFIRMED: Reservation has been confirmed by admin
 * - CANCELLED: Reservation has been cancelled
 * - COMPLETED: Reservation has been completed (guest visited)
 * - NO_SHOW: Guest did not show up for the reservation
 */
enum ReservationStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';
}

