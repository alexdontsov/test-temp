<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Exceptions;

use App\Common\Application\Exceptions\CustomException;
use Symfony\Component\HttpFoundation\Response;

class FileNotFoundException extends CustomException
{
    public function __construct(string $path)
    {
        $message = trans('settings.config.file-not-found', ['fileName' => $path]);
        parent::__construct($message, Response::HTTP_NOT_FOUND);
    }
}
