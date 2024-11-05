<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Requests;

use App\S3Bucket\Presentation\Http\Rules\BucketRenameFileExtensionRequiredRule;
use Illuminate\Foundation\Http\FormRequest;

class RenameBucketObjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'string'],
            'to' => ['required', 'string', new BucketRenameFileExtensionRequiredRule],
        ];
    }
}
