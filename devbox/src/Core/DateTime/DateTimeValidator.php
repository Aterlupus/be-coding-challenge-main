<?php
declare(strict_types=1);

namespace App\Core\DateTime;

class DateTimeValidator
{
    public static function isValid(string $dateTimeString): bool
    {
        return false !== strtotime($dateTimeString);
    }
}
