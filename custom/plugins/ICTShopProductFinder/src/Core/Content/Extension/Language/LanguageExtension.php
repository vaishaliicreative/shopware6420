<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\Extension\Language;

use ICTShopProductFinder\Core\Content\ShopProductFinder\ShopProductFinderDefinition;
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
                'ictShopProductFinderTranId',
                ShopProductFinderDefinition::class,
                'ict_shop_product_finder_id')
        );
    }
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }
}
