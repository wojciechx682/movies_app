<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PosterUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/posters')]
        private string $targetDirectory,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $extension = $file->guessExtension();

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new FileException('Nieobsługiwany format plakatu.');
        }

        $filename = bin2hex(random_bytes(16)).'.'.$extension;

        $file->move($this->targetDirectory, $filename);

        return 'uploads/posters/'.$filename;
    }
}
