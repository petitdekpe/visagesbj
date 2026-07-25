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
        $slugger = new AsciiSlugger();
        $safeName = strtolower((string) $slugger->slug($baseName));
        $filename = sprintf('%s-%s.webp', $safeName, bin2hex(random_bytes(4)));

        $this->convertToWebp($file->getPathname(), $this->targetDirectory.'/'.$filename, $file->getMimeType());

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
     * The stored file keeps its original colors — the black & white look on
     * the public site is a CSS filter (grayscale by default, full color on
     * hover), not a destructive pixel conversion. Every upload is converted
     * to WebP regardless of source format, to keep file sizes down.
     */
    private function convertToWebp(string $sourcePath, string $targetPath, ?string $mimeType): void
    {
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            'image/avif' => \function_exists('imagecreatefromavif') ? imagecreatefromavif($sourcePath) : false,
            default => false,
        };

        if (false === $image) {
            throw new \RuntimeException(sprintf('Format d\'image non supporté : "%s".', $mimeType));
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        imagewebp($image, $targetPath, 82);

        unlink($sourcePath);
    }
}
