<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use DateTime;

class Random
{
    private const INT_MAX = 2147483647;

    private const HTTP_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];


    public static function positiveInteger(int $max = self::INT_MAX): int
    {
        return mt_rand(1, $max);
    }

    public static function getString(int $length = 8, bool $alphanumeric = false): string
    {
        $x = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($alphanumeric) {
            $x .= '0123456789';
        }

        return substr(str_shuffle(str_repeat($x, (int) ceil($length/strlen($x)) )),1,$length);
    }

    public static function getPastDate(): DateTime
    {
        $currentTimestamp = time();
        $randomTimestamp = self::positiveInteger($currentTimestamp);

        return new DateTime(date( 'm/d/Y', $randomTimestamp));
    }

    public static function getHttpMethod(): string
    {
        return self::getArrayElement(self::HTTP_METHODS);
    }

    public static function getArrayElement(array $elements): mixed
    {
        $index = mt_rand(0, count($elements) - 1);
        return $elements[$index];
    }
}
