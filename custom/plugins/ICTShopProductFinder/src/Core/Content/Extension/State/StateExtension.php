<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\Extension\State;

use ICTShopProductFinder\Core\Content\ShopProductFinder\ShopProductFinderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;

use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;

class StateExtension extends EntityExtension
{

    public  function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('countryState',ShopProductFinderDefinition::class,'id')
        );
    }

    public function getDefinitionClass(): string
    {
        return CountryStateDefinition::class;
    }
}
