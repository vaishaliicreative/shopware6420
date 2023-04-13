<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\Extension\Media;

use ICTShopProductFinder\Core\Content\ShopProductFinder\ShopProductFinderDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField('media','media_id','id',ShopProductFinderDefinition::class,false)
        );
    }

    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }
}
