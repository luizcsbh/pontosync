<?php

namespace App\Services\Storage;

use App\Contracts\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LocalFileStorageService implements FileStorageInterface
{
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    public function getUrl(string $path): string
    {
        return Storage::disk('local')->url($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk('local')->delete($path);
    }
}
