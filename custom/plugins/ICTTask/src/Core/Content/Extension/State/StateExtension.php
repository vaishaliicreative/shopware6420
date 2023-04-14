<?php declare(strict_types=1);

namespace ICTTask\Core\Content\Extension\State;

use ICTTask\Core\Content\IctTask\IctTaskDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;

use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;

class StateExtension extends EntityExtension
{

    public  function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('countryState',IctTaskDefinition::class,'id')
        );
    }

    public function getDefinitionClass(): string
    {
        return CountryStateDefinition::class;
    }
}
