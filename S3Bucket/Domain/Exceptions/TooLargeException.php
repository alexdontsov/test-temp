<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Exceptions;

use App\Common\Application\Exceptions\CustomException;
use Symfony\Component\HttpFoundation\Response;

class TooLargeException extends CustomException
{
    public function __construct(?string $message = null)
    {
        $message = $message ?? trans('settings.config.object-too_large');
        parent::__construct($message, Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }
}
