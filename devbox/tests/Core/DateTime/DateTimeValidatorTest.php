<?php
declare(strict_types=1);

namespace Tests\Core\DateTime;

use App\Core\DateTime\DateTimeValidator;
use DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeValidatorTest extends TestCase
{
    public function testItProperlyValidatesDateTimeString()
    {
        self::assertTrue(DateTimeValidator::isValid('2020-01-01'));
        self::assertTrue(DateTimeValidator::isValid('1410-07-15'));
        self::assertTrue(DateTimeValidator::isValid((new DateTime())->format('c')));
        self::assertTrue(DateTimeValidator::isValid((new DateTime())->format('Y-m-d')));

        self::assertFalse(DateTimeValidator::isValid('xxx'));
        self::assertFalse(DateTimeValidator::isValid((new DateTime())->format('Y_Y-Y_Y')));
    }
}
