<?php declare(strict_types=1);

namespace ICTShopFinder\Resources\snippet\en_GB;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

/**
 * @method string getTechnicalName()
 */
class SnippetFile_en_GB implements SnippetFileInterface
{

    public function getName(): string
    {
        return 'storefront.en-GB';
    }

    public function getPath(): string
    {
        return __DIR__.'/storefront.en-GB.json';
    }

    public function getIso(): string
    {
        return 'en-GB';
    }

    public function getAuthor(): string
    {
        return 'ICTShopFinder';
    }

    public function isBase(): bool
    {
        return false;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getTechnicalName()
    }
}
