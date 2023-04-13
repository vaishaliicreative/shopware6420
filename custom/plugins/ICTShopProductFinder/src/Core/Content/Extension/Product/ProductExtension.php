<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\Extension\Product;

use ICTShopProductFinder\Core\Content\ShopProductFinder\ShopProductFinderDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('product', ShopProductFinderDefinition::class, 'id')
        );
    }
    public function getDefinitionClass(): string
    {
       return ProductDefinition::class;
    }
}
