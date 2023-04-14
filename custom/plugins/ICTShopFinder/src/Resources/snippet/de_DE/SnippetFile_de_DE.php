<?php declare(strict_types=1);

namespace ICTShopFinder\Resources\snippet\de_DE;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

/**
 * @method string getTechnicalName()
 */
class SnippetFile_de_DE implements SnippetFileInterface
{

    public function getName(): string
    {
        return 'storefront.de-DE';
    }

    public function getPath(): string
    {
        return __DIR__.'/storefront.de-DE.json';
    }

    public function getIso(): string
    {
        return 'de-DE';
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
