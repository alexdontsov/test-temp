<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Resources;

use App\S3Bucket\Domain\Models\Entities\Bucket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BucketResource extends JsonResource
{
    /**
     * @return array<string, int>
     */
    public function toArray(Request $request): array
    {
        /** @var Bucket $resource */
        $resource = $this->resource;

        return [
            'name' => $resource->getName(),
            'total' => $resource->getObjectCount(),
            'size' => $resource->getSize(),
        ];
    }
}
