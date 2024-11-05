<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Exceptions;

use App\Common\Application\Exceptions\CustomValidationException;

/**
 * Когда при экспорте часть указанных файлов или папок не существует
 */
class ObjectsNotFoundException extends CustomValidationException
{
}
