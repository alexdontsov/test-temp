<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Resources;

use App\S3Bucket\Domain\Models\Entities\FolderObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var FolderObject $resource */
        $object = $this->resource;

        return [
            'path' => $object->getPath(),
            'type' => $object->getType(),
            'objects_count' => $object->getObjectsCount(),
            'size' => $object->getSize(),
        ];
    }
}
