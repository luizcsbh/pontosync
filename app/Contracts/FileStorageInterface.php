<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    /**
     * Armazena o arquivo e retorna o caminho relativo.
     */
    public function store(UploadedFile $file, string $directory): string;

    /**
     * Retorna a URL pública ou temporária do arquivo.
     */
    public function getUrl(string $path): string;

    /**
     * Remove o arquivo do storage.
     */
    public function delete(string $path): bool;
}
