<?php
declare(strict_types=1);

namespace App\Core\File;

use Iterator;
use SplFileObject;

class FileHandler
{
    public static function getIterator(string $filePath, int $skipLines = 0): Iterator
    {
        $file = new SplFileObject($filePath, 'r');

        $file->seek($skipLines);

        while (false === $file->eof()) {
            yield $file->fgets();
        }

        return null;
    }
}
