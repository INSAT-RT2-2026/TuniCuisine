<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class RecipeImageUploader
{
    private const MAX_BYTES = 2_097_152;

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private readonly string $targetDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(?UploadedFile $file, string $recipeName): ?string
    {
        if ($file === null) {
            return null;
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new FileException('Image must be 2 MB or smaller.');
        }

        $extension = $this->resolveExtension($file);
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = $this->slugger->slug($original ?: $recipeName)->lower()->toString();
        $fileName = sprintf('%s-%s.%s', $safe ?: 'recipe', uniqid('', true), $extension);

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new FileException('Could not upload image: '.$e->getMessage(), 0, $e);
        }

        return 'images/recipes/'.$fileName;
    }

    private function resolveExtension(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        return match ($ext) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => throw new FileException('Only JPG, PNG, and WebP images are allowed.'),
        };
    }
}
