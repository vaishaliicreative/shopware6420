<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Content\Extension\Language;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{

    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }
}
