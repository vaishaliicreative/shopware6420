<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Content\Extension\Language;

use ICTShopFinder\Core\Content\ShopFinder\Aggregate\ShopFinderTranslation\ShopFinderTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'ictShopFinderTranId',
                ShopFinderTranslationDefinition::class,
                'ict_shop_finder_id')
        );
    }

    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }
}
