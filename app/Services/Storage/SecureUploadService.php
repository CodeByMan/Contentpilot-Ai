<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class SecureUploadService
{
    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MAX_IMAGE_DIMENSION = 4096;

    private const MAX_IMAGE_PIXELS = 16_777_216;

    private const DEFAULT_MAX_IMAGE_BYTES = 8_388_608;

    private const MAX_RECEIPT_BYTES = 5_242_880;

    public function storeImage(
        UploadedFile $file,
        string $directory,
        int $maxWidth,
        int $maxHeight,
        string $disk = 'public',
        bool $cover = false,
        int $maxBytes = self::DEFAULT_MAX_IMAGE_BYTES,
    ): string {
        $realPath = $this->validatedRealPath($file, $maxBytes);
        $mimeType = (string) $file->getMimeType();

        if (! in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES, true)) {
            throw new RuntimeException('The uploaded file is not a supported image.');
        }

        $dimensions = @getimagesize($realPath);

        if ($dimensions === false) {
            throw new RuntimeException('The uploaded image could not be decoded safely.');
        }

        [$sourceWidth, $sourceHeight] = $dimensions;

        if (
            $sourceWidth < 1
            || $sourceHeight < 1
            || $sourceWidth > self::MAX_IMAGE_DIMENSION
            || $sourceHeight > self::MAX_IMAGE_DIMENSION
            || ($sourceWidth * $sourceHeight) > self::MAX_IMAGE_PIXELS
        ) {
            throw new RuntimeException('The uploaded image dimensions are outside the safe processing limits.');
        }

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($realPath);
            $processed = $cover
                ? $image->cover($maxWidth, $maxHeight)
                : $image->scaleDown(width: $maxWidth, height: $maxHeight);
            $encoded = $processed->toWebp(82);
        } catch (Throwable $exception) {
            throw new RuntimeException('The uploaded image could not be processed safely.', 0, $exception);
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';

        if (! Storage::disk($disk)->put($path, (string) $encoded)) {
            throw new RuntimeException('The image could not be stored.');
        }

        return $path;
    }

    public function storePrivateReceipt(UploadedFile $file): string
    {
        $realPath = $this->validatedRealPath($file, self::MAX_RECEIPT_BYTES);
        $mimeType = (string) $file->getMimeType();

        if (in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES, true)) {
            return $this->storeImage(
                $file,
                'payment-receipts',
                2400,
                2400,
                'private',
                maxBytes: self::MAX_RECEIPT_BYTES,
            );
        }

        if ($mimeType !== 'application/pdf' || ! $this->hasPdfSignature($realPath)) {
            throw new RuntimeException('The payment receipt must be a valid image or PDF document.');
        }

        $path = 'payment-receipts/'.Str::uuid().'.pdf';
        $stream = fopen($realPath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('The payment receipt could not be read.');
        }

        try {
            if (! Storage::disk('private')->put($path, $stream)) {
                throw new RuntimeException('The payment receipt could not be stored.');
            }
        } finally {
            fclose($stream);
        }

        return $path;
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk($disk)->delete($path);
        } catch (Throwable $exception) {
            Log::warning('Stored file cleanup failed.', [
                'disk' => $disk,
                'exception_class' => $exception::class,
            ]);
        }
    }

    private function validatedRealPath(UploadedFile $file, int $maxBytes): string
    {
        if (! $file->isValid()) {
            throw new RuntimeException('The uploaded file is invalid.');
        }

        $size = $file->getSize();

        if (! is_int($size) || $size < 1 || $size > $maxBytes) {
            throw new RuntimeException('The uploaded file size is outside the permitted limit.');
        }

        $realPath = $file->getRealPath();

        if (! is_string($realPath) || ! is_file($realPath) || ! is_readable($realPath)) {
            throw new RuntimeException('The uploaded file could not be read.');
        }

        return $realPath;
    }

    private function hasPdfSignature(string $realPath): bool
    {
        $stream = fopen($realPath, 'rb');

        if ($stream === false) {
            return false;
        }

        try {
            return fread($stream, 5) === '%PDF-';
        } finally {
            fclose($stream);
        }
    }
}
