<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Reserved = 'reserved';
    case Committed = 'committed';
    case Released = 'released';
}
