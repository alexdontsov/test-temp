<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteBucketObjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:file,folder'],
            'path' => ['required', 'string'],
        ];
    }
}
