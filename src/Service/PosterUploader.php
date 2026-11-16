<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PosterUploader
{
    private const POSTER_PATH_PREFIX = 'uploads/posters/';

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/posters')]
        private string $targetDirectory,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $extension = $file->guessExtension();

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new FileException('Nieobsługiwany format plakatu');
        }

        $filename = bin2hex(random_bytes(16)).'.'.$extension;

        $file->move($this->targetDirectory, $filename);

        // Previous return value:
        // return 'uploads/posters/'.$filename;
        return self::POSTER_PATH_PREFIX.$filename;
    }

    public function delete(?string $posterPath): bool
    {
        if ($posterPath === null) {
            return true;
        }

        $posterPath = str_replace('\\', '/', $posterPath);

        if (!str_starts_with($posterPath, self::POSTER_PATH_PREFIX)) {
            return false;
        }

        $filename = basename($posterPath);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return false;
        }

        $posterFilePath = $this->targetDirectory.DIRECTORY_SEPARATOR.$filename;

        if (!is_file($posterFilePath)) {
            return true;
        }

        return @unlink($posterFilePath);
    }
}
