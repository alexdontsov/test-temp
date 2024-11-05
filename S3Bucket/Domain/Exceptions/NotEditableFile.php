<?php

declare(strict_types=1);

namespace App\S3Bucket\Domain\Exceptions;

use App\Common\Application\Exceptions\CustomException;
use Symfony\Component\HttpFoundation\Response;

class NotEditableFile extends CustomException
{
    public function __construct()
    {
        $message = trans('settings.config.file-cant-edit');
        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }
}
