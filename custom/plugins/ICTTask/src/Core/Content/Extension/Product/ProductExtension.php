<?php declare(strict_types=1);

namespace ICTTask\Core\Content\Extension\Product;

use ICTTask\Core\Content\IctTask\IctTaskDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('product', IctTaskDefinition::class, 'id')
        );
    }
    public function getDefinitionClass(): string
    {
       return ProductDefinition::class;
    }
}
