<?php declare(strict_types=1);
namespace ICTShopFinder\Core\Content\Extension\Country;

use ICTShopFinder\Core\Content\ShopFinder\ShopFinderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\CountryDefinition;

class CountryExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('shopFinderIds', ShopFinderDefinition::class, 'country_id')
        );
    }
    public function getDefinitionClass(): string
    {
        return CountryDefinition::class;
    }
}
