<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Struct;

use Shopware\Core\Framework\Struct\Struct;

class PdfFile extends Struct
{
    private string $fileName;
    private string $blobContent;

    public function __construct(string $fileName, string $blobContent)
    {
        $this->fileName = $fileName;
        $this->blobContent = $blobContent;
    }

    public function getBlobContent(): string
    {
        return $this->blobContent;
    }

    public function setBlobContent(string $blobContent): void
    {
        $this->blobContent = $blobContent;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }
}
