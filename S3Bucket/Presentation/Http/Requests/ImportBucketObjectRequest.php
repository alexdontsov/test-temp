<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Requests;

use App\S3Bucket\Presentation\Http\Rules\BucketImportFileExtensionRequiredRule;
use App\S3Bucket\Presentation\Http\Rules\BucketImportFileSizeRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportBucketObjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bucket = $this->route('bucket');

        return [
            'path' => ['required', 'string'],
            'attachment' => [
                'required', 'file', new BucketImportFileExtensionRequiredRule, new BucketImportFileSizeRule($bucket)
            ],
        ];
    }
}
