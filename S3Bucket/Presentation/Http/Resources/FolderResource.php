<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Resources;

use App\S3Bucket\Domain\Models\ObjectInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class FolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ObjectInterface $resource */
        $fileObject = $this->resource;

        return [
            'path' => $fileObject->getPath(),
            'type' => $fileObject->getType(),
            'objects_count' => $fileObject->getObjectsCount(),
            'size' => $fileObject->getSize(),
            'items' => $this->buildItems($fileObject),
        ];
    }

    private function buildItems(ObjectInterface $fileObject): array
    {
        return Arr::map($fileObject->getChildObjects(), function (ObjectInterface $object) {
            return match ($object->getType()) {
                ObjectInterface::FILE =>  FileItemResource::make($object),
                ObjectInterface::FOLDER =>  FolderItemResource::make($object),
            };
        });
    }
}
