<?php

declare(strict_types=1);

use App\User\Domain\Enum\PermissionEnum;
use App\S3Bucket\Presentation\Http\Controllers\CreateBucketObjectController;
use App\S3Bucket\Presentation\Http\Controllers\DeleteBucketObjectController;
use App\S3Bucket\Presentation\Http\Controllers\ExportObjectController;
use App\S3Bucket\Presentation\Http\Controllers\GetBucketsController;
use App\S3Bucket\Presentation\Http\Controllers\GetObjectListController;
use App\S3Bucket\Presentation\Http\Controllers\ImportFileController;
use App\S3Bucket\Presentation\Http\Controllers\RenameBucketObjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings/buckets')
    ->name('settings.buckets.')
    ->middleware(['permission:' . PermissionEnum::CONFIG_VIEW->value])
    ->group(function () {
        Route::get('/', GetBucketsController::class)->name('index');

        Route::prefix('/{bucket}/object')
            ->name('object.')
            ->group(function () {
                Route::get('/', GetObjectListController::class)->name('list')->where('path', '.*');
                Route::post('/export', ExportObjectController::class)->name('export')->where('path', '.*');

                Route::middleware(['permission:' . PermissionEnum::CONFIG_EDIT->value])->group(function () {
                    Route::post('/', CreateBucketObjectController::class)->name('create');
                    Route::put('/', RenameBucketObjectController::class)->name('rename');
                    Route::delete('/', DeleteBucketObjectController::class)->name('delete');
                    Route::post('/import', ImportFileController::class)->name('import')->where('path', '.*');
                });
            });
    });
