<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Enums;

use App\Common\Application\Enums\EnumEnhancements;

enum BucketObjectEnum: string
{
    use EnumEnhancements;

    case FILE = 'file';
    case FOLDER = 'folder';
    case ROOT = 'root';

    public function description(): string
    {
        return match($this)
        {
            self::FILE => 'файл',
            self::FOLDER => 'папка',
            self::ROOT => 'корень бакета',
        };
    }
}
