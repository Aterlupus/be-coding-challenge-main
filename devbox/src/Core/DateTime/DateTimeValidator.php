<?php
declare(strict_types=1);
/**
 * This file is a part of BibleAgora package.
 * Author: Artur Siedlecki
 * Date: 29.09.2022
 * Time: 16:49
 */

namespace App\Core\DateTime;

class DateTimeValidator
{
    public static function isValid(string $dateTimeString): bool
    {
        return false !== strtotime($dateTimeString);
    }
}
