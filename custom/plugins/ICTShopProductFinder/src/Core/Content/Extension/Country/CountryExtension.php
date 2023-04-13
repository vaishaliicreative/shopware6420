<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\Extension\Country;

use ICTShopProductFinder\Core\Content\ShopProductFinder\ShopProductFinderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\CountryDefinition;

class CountryExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('country', ShopProductFinderDefinition::class, 'id')
        );
    }
    public function getDefinitionClass(): string
    {
        return CountryDefinition::class;
    }
}
