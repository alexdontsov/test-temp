<?php

declare(strict_types=1);

namespace App\S3Bucket\Presentation\Http\Resources;

use App\S3Bucket\Domain\Models\ObjectInterface;
use App\S3Bucket\Domain\Models\Entities\FolderObject;
use App\S3Bucket\Domain\Models\Entities\FileObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BucketObjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ObjectInterface $resource */
        $resource = $this->resource;

        // Для файла выведем всю информаицю сразу
        if ($resource->getType() == ObjectInterface::FILE) {
            /** @var FileObject $resource */
            return $this->fullFileResource($resource);
        }

        /** @var FolderObject $resource */
        return $this->fullDirResource($resource);
    }

    private function fullFileResource(FileObject $fileObject): array
    {
        return [
            'path' => $fileObject->getPath(),
            'type' => $fileObject->getType(),
            'updated_at' => $fileObject->getUpdatedAt()->format('Y-m-d H:i:s'),
            'size' => $fileObject->getSize(),
            'body' => $fileObject->getBody(),
        ];
    }

    private function fullDirResource(FolderObject $fileObject): array
    {
        $children = [];
        /** @var ObjectInterface $childObject */
        foreach ($fileObject->getChildObjects() as $childObject) {
            if ($childObject->getType() == ObjectInterface::FILE) {
                /** @var FileObject $childObject */
                $children[] = $this->buildFileItm($childObject);
            } else {
                /** @var FolderObject $childObject */
                $children[] = $this->buildDirItm($childObject);
            }
        }

        return [
            'path' => $fileObject->getPath(),
            'type' => $fileObject->getType(),
            'objects_count' => $fileObject->getObjectsCount(),
            'size' => $fileObject->getSize(),
            'children' => $children,
        ];
    }

    private function buildFileItm(FileObject $object): array
    {
        return [
            'path' => $object->getPath(),
            'type' => $object->getType(),
            'updated_at' => $object->getUpdatedAt()->format('Y-m-d H:i:s'),
            'size' => $object->getSize(),
        ];
    }

    private function buildDirItm(FolderObject $object): array
    {
        return [
            'path' => $object->getPath(),
            'type' => $object->getType(),
            'objects_count' => $object->getObjectsCount(),
            'size' => $object->getSize(),
        ];
    }
}
