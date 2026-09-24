<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadImage
{
    public function execute(UploadedFile $file, string $filename, ?array $dimensions = null): string
    {
        if (!$dimensions) {
            $path = Storage::disk('public')->putFileAs('uploads', $file, basename($filename));

            return 'storage/' . $path;
        }

        [$width, $height] = $dimensions;
        $source = $file->getPathname();
        $mime = $file->getMimeType();
        $image = $mime === 'image/png' ? imagecreatefrompng($source) : imagecreatefromjpeg($source);

        if (!$image) {
            throw new \RuntimeException('Unable to read the uploaded image.');
        }

        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        ob_start();
        imagepng($resized);
        $contents = ob_get_clean();
        $path = 'uploads/' . basename($filename);
        Storage::disk('public')->put($path, $contents);

        imagedestroy($image);
        imagedestroy($resized);

        return 'storage/' . $path;
    }

    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        } elseif (is_file(public_path($path))) {
            unlink(public_path($path));
        }
    }
}
