<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PersonnalitePhotoUploader
{
    public function __construct(
        private readonly string $targetDirectory,
    ) {
    }

    /**
     * Accepts any File — an UploadedFile from an HTTP form, or a plain File
     * built from a path on disk (bulk CLI import) — same processing either way.
     */
    public function upload(File $file, string $baseName): string
    {
        $mimeType = $file->getMimeType();

        $slugger = new AsciiSlugger();
        $safeName = strtolower((string) $slugger->slug($baseName));
        $extension = $file->guessExtension() ?: $file->getExtension();
        $filename = sprintf('%s-%s.%s', $safeName, bin2hex(random_bytes(4)), $extension);

        $file->move($this->targetDirectory, $filename);

        $this->convertToGrayscale($this->targetDirectory.'/'.$filename, $mimeType);

        return $filename;
    }

    public function remove(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectory.'/'.$filename;
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Photos are displayed in black & white across the site (matches the
     * campaign's visual identity), so convert on upload rather than relying
     * on a CSS filter — the stored file itself becomes grayscale.
     */
    private function convertToGrayscale(string $path, ?string $mimeType): void
    {
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => false,
        };

        if (false === $image) {
            return;
        }

        imagepalettetotruecolor($image);

        if ('image/png' === $mimeType) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);

        match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $path, 90),
            'image/png' => imagepng($image, $path),
            'image/webp' => imagewebp($image, $path, 90),
        };

        imagedestroy($image);
    }
}
