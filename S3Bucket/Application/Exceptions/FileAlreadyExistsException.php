<?php

declare(strict_types=1);

namespace App\S3Bucket\Application\Exceptions;

use App\Common\Application\Exceptions\CustomException;
use Symfony\Component\HttpFoundation\Response;

class FileAlreadyExistsException extends CustomException
{
    public function __construct(string $path, string $fileName)
    {
        $message = trans('settings.config.already-exists', ['dirName' => $path, 'fileName' => $fileName]);
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
